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

    public static function getInteretsParMois($id_ef, $date_debut, $date_fin) {
        try {
            if (strtotime($date_debut) > strtotime($date_fin)) {
                throw new Exception('La date de début doit être antérieure à la date de fin');
            }

            $db = getDB();
            $sql = "
                SELECT 
                    p.id,
                    p.montant,
                    p.date_pret,
                    p.duree_mois,
                    p.assurance,
                    p.delai_premier_remboursement,
                    t.taux
                FROM pret p
                JOIN type_pret t ON p.id_type_pret = t.id
                WHERE p.id_ef = ?
                  AND p.valide = 1
                  AND (
                    (p.date_pret BETWEEN ? AND ?) OR
                    (DATE_ADD(p.date_pret, INTERVAL p.delai_premier_remboursement MONTH) BETWEEN ? AND ?) OR
                    (DATE_ADD(p.date_pret, INTERVAL p.delai_premier_remboursement + p.duree_mois - 1 MONTH) >= ?)
                  )
                ORDER BY p.date_pret
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id_ef, $date_debut, $date_fin, $date_debut, $date_fin, $date_debut]);
            $prets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $interetsParMois = [];
            
            foreach ($prets as $pret) {
                $tableauAmortissement = self::genererTableauAmortissement(
                    $pret['montant'],
                    $pret['taux'],
                    $pret['duree_mois'],
                    $pret['date_pret'],
                    $pret['assurance'],
                    $pret['delai_premier_remboursement']
                );
                
                foreach ($tableauAmortissement as $echeance) {
                    $mois = date('Y-m', strtotime($echeance['date']));
                    
                    if ($mois >= date('Y-m', strtotime($date_debut)) && 
                        $mois <= date('Y-m', strtotime($date_fin))) {
                        
                        if (!isset($interetsParMois[$mois])) {
                            $interetsParMois[$mois] = 0;
                        }
                        $interetsParMois[$mois] += $echeance['interets'];
                    }
                }
            }
            
            $resultat = [];
            foreach ($interetsParMois as $mois => $interets) {
                $resultat[] = [
                    'mois' => $mois,
                    'interets' => round($interets, 2)
                ];
            }
            
            usort($resultat, function($a, $b) {
                return strcmp($a['mois'], $b['mois']);
            });
            
            return $resultat;
        } catch (Exception $e) {
            throw new Exception('Erreur lors du calcul des intérêts: ' . $e->getMessage());
        }
    }

    public static function calculerAnnuiteConstante($capital, $tauxAnnuel, $dureeMois, $assurance = 0) {
        try {
            if ($capital <= 0 || $dureeMois <= 0) {
                throw new Exception('Capital et durée doivent être positifs');
            }
            if ($tauxAnnuel < 0 || $tauxAnnuel > 100) {
                throw new Exception('Taux invalide (doit être entre 0 et 100%)');
            }
            if ($assurance < 0 || $assurance > 10) {
                throw new Exception('Assurance invalide (doit être entre 0 et 10%)');
            }
            
            $tauxMensuel = $tauxAnnuel / 100 / 12;
            $assuranceMensuelle = ($capital * $assurance / 100) / $dureeMois;
            
            if ($tauxMensuel == 0) {
                return round(($capital / $dureeMois) + $assuranceMensuelle, 2);
            }
            
            $denominateur = pow(1 + $tauxMensuel, $dureeMois) - 1;
            if ($denominateur == 0) {
                throw new Exception('Erreur de calcul: dénominateur nul');
            }
            
            $annuite = $capital * ($tauxMensuel * pow(1 + $tauxMensuel, $dureeMois)) / $denominateur;
            return round($annuite + $assuranceMensuelle, 2);
        } catch (Exception $e) {
            throw new Exception('Erreur lors du calcul de l\'annuité: ' . $e->getMessage());
        }
    }

    public static function genererTableauAmortissement($capital, $tauxAnnuel, $dureeMois, $datePremierPret, $assurance = 0, $delaiPremierRemboursement = 0) {
        try {
            if ($capital <= 0 || $dureeMois <= 0) {
                throw new Exception('Capital et durée doivent être positifs');
            }
            if ($tauxAnnuel < 0 || $tauxAnnuel > 100) {
                throw new Exception('Taux invalide (doit être entre 0 et 100%)');
            }
            if ($assurance < 0 || $assurance > 10) {
                throw new Exception('Assurance invalide (doit être entre 0 et 10%)');
            }
            if ($delaiPremierRemboursement < 0 || $delaiPremierRemboursement > 60) {
                throw new Exception('Délai invalide (doit être entre 0 et 60 mois)');
            }
            
            $tableau = [];
            $capitalRestant = $capital;
            $tauxMensuel = $tauxAnnuel / 100 / 12;
            $assuranceMensuelle = ($capital * $assurance / 100) / $dureeMois;
            $annuite = self::calculerAnnuiteConstante($capital, $tauxAnnuel, $dureeMois, 0); // Sans assurance
            
            $datePremiereEcheance = date('Y-m-d', strtotime($datePremierPret . ' + ' . $delaiPremierRemboursement . ' months'));
            
            for ($mois = 1; $mois <= $dureeMois; $mois++) {
                $dateEcheance = date('Y-m-d', strtotime($datePremiereEcheance . ' + ' . ($mois - 1) . ' months'));
                
                // Dernière échéance : ajustement pour éviter les reliquats
                if ($mois == $dureeMois) {
                    $amortissement = $capitalRestant;
                    $interets = $annuite - $amortissement;
                    $capitalRestant = 0;
                } else {
                    $interets = $capitalRestant * $tauxMensuel;
                    $amortissement = $annuite - $interets;
                    $capitalRestant -= $amortissement;
                }
                
                $tableau[] = [
                    'echeance' => $mois,
                    'date' => $dateEcheance,
                    'annuite' => round($annuite + $assuranceMensuelle, 2),
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

    public static function validerPret($id, $valide) {
        try {
            $db = getDB();
            $stmt = $db->prepare("UPDATE pret SET valide = ? WHERE id = ?");
            $stmt->execute([$valide ? 1 : 0, $id]);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la validation du prêt: ' . $e->getMessage());
        }
    }

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