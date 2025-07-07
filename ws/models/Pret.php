<?php
require_once __DIR__ . '/../db.php';

class Pret {
    public static function getAll() {
        $db = getDB();
        $stmt = $db->query("SELECT p.id, e.nom AS nom_etudiant, e.prenom, t.nom AS nom_type, t.taux, p.montant, p.date_pret, p.id_etudiant, p.id_ef, p.id_type_pret, ef.nom AS nom_banque, p.duree_mois, p.assurance, p.delai_premier_remboursement, p.valide FROM pret p JOIN etudiant e ON p.id_etudiant = e.id JOIN type_pret t ON p.id_type_pret = t.id JOIN etablissement_financier ef ON p.id_ef = ef.id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM pret WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        try {
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO pret (id_etudiant, id_ef, id_type_pret, montant, date_pret, duree_mois, assurance, delai_premier_remboursement, valide) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data->id_etudiant, 
                $data->id_ef, 
                $data->id_type_pret, 
                $data->montant, 
                $data->date_pret,
                $data->duree_mois ?? 12,
                $data->assurance ?? 0,
                $data->delai_premier_remboursement ?? 0,
                $data->valide ?? 0
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la création du prêt: ' . $e->getMessage());
        }
    }

    public static function update($id, $data) {
        try {
            $db = getDB();
            $stmt = $db->prepare("UPDATE pret SET id_etudiant = ?, id_ef = ?, id_type_pret = ?, montant = ?, date_pret = ?, duree_mois = ?, assurance = ?, delai_premier_remboursement = ?, valide = ? WHERE id = ?");
            $stmt->execute([
                $data->id_etudiant, 
                $data->id_ef, 
                $data->id_type_pret, 
                $data->montant, 
                $data->date_pret,
                $data->duree_mois ?? 12,
                $data->assurance ?? 0,
                $data->delai_premier_remboursement ?? 0,
                $data->valide ?? 0,
                $id
            ]);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la mise à jour du prêt: ' . $e->getMessage());
        }
    }

    public static function delete($id) {
        try {
            $db = getDB();
            $stmt = $db->prepare("DELETE FROM pret WHERE id = ?");
            $stmt->execute([$id]);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la suppression du prêt: ' . $e->getMessage());
        }
    }

    // Calcule les intérêts gagnés par mois pour un EF donné (seulement les prêts validés)
    public static function getInteretsParMois($id_ef, $date_debut, $date_fin) {
        try {
            $db = getDB();
            $sql = "
                SELECT 
                    DATE_FORMAT(p.date_pret, '%Y-%m') AS mois,
                    SUM(p.montant * t.taux / 100) AS interets
                FROM pret p
                JOIN type_pret t ON p.id_type_pret = t.id
                WHERE p.id_ef = ?
                  AND p.date_pret BETWEEN ? AND ?
                  AND p.valide = 1
                GROUP BY mois
                ORDER BY mois
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id_ef, $date_debut, $date_fin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Erreur lors du calcul des intérêts: ' . $e->getMessage());
        }
    }

    // Calcule l'annuité constante selon la formule : A = C * (i * (1+i)^n) / ((1+i)^n - 1)
    // où C = capital, i = taux mensuel, n = nombre d'échéances
    public static function calculerAnnuiteConstante($capital, $tauxAnnuel, $dureeMois, $assurance = 0) {
        try {
            if ($capital <= 0 || $dureeMois <= 0) {
                throw new Exception('Capital et durée doivent être positifs');
            }
            
            $tauxMensuel = $tauxAnnuel / 100 / 12;
            $assuranceMensuelle = ($capital * $assurance / 100) / $dureeMois;
            
            if ($tauxMensuel == 0) {
                $annuite = $capital / $dureeMois;
            } else {
                // Protection contre les erreurs de calcul
                $denominateur = pow(1 + $tauxMensuel, $dureeMois) - 1;
                if ($denominateur == 0) {
                    throw new Exception('Erreur de calcul: dénominateur nul');
                }
                $annuite = $capital * ($tauxMensuel * pow(1 + $tauxMensuel, $dureeMois)) / $denominateur;
            }
            
            return $annuite + $assuranceMensuelle;
        } catch (Exception $e) {
            throw new Exception('Erreur lors du calcul de l\'annuité: ' . $e->getMessage());
        }
    }

    // Génère le tableau d'amortissement complet
    public static function genererTableauAmortissement($capital, $tauxAnnuel, $dureeMois, $datePremierPret, $assurance = 0, $delaiPremierRemboursement = 0) {
        try {
            if ($capital <= 0 || $dureeMois <= 0) {
                throw new Exception('Capital et durée doivent être positifs');
            }
            
            $tableau = [];
            $capitalRestant = $capital;
            $tauxMensuel = $tauxAnnuel / 100 / 12;
            $assuranceMensuelle = ($capital * $assurance / 100) / $dureeMois;
            
            // Calcul de l'annuité constante
            if ($tauxMensuel == 0) {
                $annuite = $capital / $dureeMois;
            } else {
                $denominateur = pow(1 + $tauxMensuel, $dureeMois) - 1;
                if ($denominateur == 0) {
                    throw new Exception('Erreur de calcul: dénominateur nul');
                }
                $annuite = $capital * ($tauxMensuel * pow(1 + $tauxMensuel, $dureeMois)) / $denominateur;
            }
            
            $annuiteTotale = $annuite + $assuranceMensuelle;
            
            // Date de la première échéance (après le délai)
            $datePremiereEcheance = date('Y-m-d', strtotime($datePremierPret . ' + ' . $delaiPremierRemboursement . ' months'));
            
            for ($mois = 1; $mois <= $dureeMois; $mois++) {
                $dateEcheance = date('Y-m-d', strtotime($datePremiereEcheance . ' + ' . ($mois - 1) . ' months'));
                
                if ($tauxMensuel == 0) {
                    $interets = 0;
                    $amortissement = $annuite;
                } else {
                    $interets = $capitalRestant * $tauxMensuel;
                    $amortissement = $annuite - $interets;
                }
                
                $capitalRestant -= $amortissement;
                
                $tableau[] = [
                    'echeance' => $mois,
                    'date' => $dateEcheance,
                    'annuite' => round($annuiteTotale, 2),
                    'amortissement' => round($amortissement, 2),
                    'interets' => round($interets, 2),
                    'assurance' => round($assuranceMensuelle, 2),
                    'capital_restant' => round(max(0, $capitalRestant), 2)
                ];
            }
            
            return $tableau;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la génération du tableau d\'amortissement: ' . $e->getMessage());
        }
    }

    // Valide ou invalide un prêt
    public static function validerPret($id, $valide) {
        try {
            $db = getDB();
            $stmt = $db->prepare("UPDATE pret SET valide = ? WHERE id = ?");
            $stmt->execute([$valide ? 1 : 0, $id]);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la validation du prêt: ' . $e->getMessage());
        }
    }

    // Récupère les prêts par étudiant
    public static function getPretsParEtudiant($id_etudiant) {
        try {
            $db = getDB();
            $stmt = $db->prepare("
                SELECT p.*, t.nom AS nom_type, t.taux, ef.nom AS nom_ef 
                FROM pret p 
                JOIN type_pret t ON p.id_type_pret = t.id 
                JOIN etablissement_financier ef ON p.id_ef = ef.id 
                WHERE p.id_etudiant = ?
                ORDER BY p.date_pret DESC
            ");
            $stmt->execute([$id_etudiant]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la récupération des prêts: ' . $e->getMessage());
        }
    }
} 