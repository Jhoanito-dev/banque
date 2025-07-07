<?php
require_once __DIR__ . '/../models/Pret.php';
require_once dirname(__DIR__, 2) . '/lib/fpdf.php';

class PretController {
    public static function getAll() {
        try {
            $result = Pret::getAll();
            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
        }
        exit;
    }

    public static function getById($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'ID invalide']);
                exit;
            }
            $result = Pret::getById($id);
            if (!$result) {
                http_response_code(404);
                echo json_encode(['error' => 'Prêt introuvable']);
                exit;
            }
            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
        }
        exit;
    }

    public static function create() {
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input);
            if (!$data) {
                http_response_code(400);
                echo json_encode(['error' => 'Données JSON invalides']);
                exit;
            }
            // Validation des données obligatoires
            if (!isset($data->id_etudiant) || !isset($data->id_ef) || !isset($data->id_type_pret) || !isset($data->montant) || !isset($data->date_pret)) {
                http_response_code(400);
                echo json_encode(['error' => 'Données manquantes']);
                exit;
            }
            $id = Pret::create($data);
            header('Content-Type: application/json');
            echo json_encode(['id' => $id]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
        }
        exit;
    }

    public static function update($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'ID invalide']);
                exit;
            }
            $input = file_get_contents('php://input');
            $data = json_decode($input);
            if (!$data) {
                http_response_code(400);
                echo json_encode(['error' => 'Données JSON invalides']);
                exit;
            }
            Pret::update($id, $data);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
        }
        exit;
    }

    public static function delete($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'ID invalide']);
                exit;
            }
            Pret::delete($id);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
        }
        exit;
    }

    // Valider ou refuser un prêt
    public static function validerPret($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'ID invalide']);
                exit;
            }
            $input = file_get_contents('php://input');
            $data = json_decode($input);
            if (!$data) {
                http_response_code(400);
                echo json_encode(['error' => 'Données JSON invalides']);
                exit;
            }
            $valide = isset($data->valide) ? (int)$data->valide : 1;
            Pret::validerPret($id, $valide);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
        }
        exit;
    }

    // Simulation de prêt (API)
    public static function simulerPret() {
        try {
            // On accepte GET ou POST
            $params = $_SERVER['REQUEST_METHOD'] === 'POST' ? json_decode(file_get_contents('php://input'), true) : $_GET;
            if (!$params) {
                http_response_code(400);
                echo json_encode(['error' => 'Paramètres invalides']);
                exit;
            }
            
            $capital = isset($params['montant']) ? floatval($params['montant']) : 0;
            $taux = isset($params['taux']) ? floatval($params['taux']) : 0;
            $duree = isset($params['duree']) ? intval($params['duree']) : 0;
            $date = isset($params['date_pret']) ? $params['date_pret'] : date('Y-m-d');
            $assurance = isset($params['assurance']) ? floatval($params['assurance']) : 0;
            $delai = isset($params['delai_premier_remboursement']) ? intval($params['delai_premier_remboursement']) : 0;
            
            // Validation stricte des paramètres
            if ($capital <= 0 || $capital > 10000000) { // Max 10M
                http_response_code(400);
                echo json_encode(['error' => 'Montant invalide (doit être entre 0 et 10M)']);
                exit;
            }
            if ($taux < 0 || $taux > 100) { // Max 100%
                http_response_code(400);
                echo json_encode(['error' => 'Taux invalide (doit être entre 0 et 100%)']);
                exit;
            }
            if ($duree <= 0 || $duree > 360) { // Max 30 ans
                http_response_code(400);
                echo json_encode(['error' => 'Durée invalide (doit être entre 1 et 360 mois)']);
                exit;
            }
            if ($assurance < 0 || $assurance > 10) { // Max 10%
                http_response_code(400);
                echo json_encode(['error' => 'Assurance invalide (doit être entre 0 et 10%)']);
                exit;
            }
            if ($delai < 0 || $delai > 60) { // Max 5 ans
                http_response_code(400);
                echo json_encode(['error' => 'Délai invalide (doit être entre 0 et 60 mois)']);
                exit;
            }
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                http_response_code(400);
                echo json_encode(['error' => 'Date invalide']);
                exit;
            }
            
            $tableau = Pret::genererTableauAmortissement($capital, $taux, $duree, $date, $assurance, $delai);
            $cout_total = array_sum(array_column($tableau, 'annuite'));
            header('Content-Type: application/json');
            echo json_encode([
                'tableau' => $tableau,
                'cout_total' => round($cout_total, 2)
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
        }
        exit;
    }

    // Génération PDF
    public static function genererPDFPret($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'ID invalide']);
                exit;
            }
            
            $pret = Pret::getById($id);
            if (!$pret) {
                http_response_code(404);
                echo json_encode(['error' => 'Prêt introuvable']);
                exit;
            }
            // Ajout du contrôle de validation du prêt
            if (!isset($pret['valide']) || $pret['valide'] != 1) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['error' => "Vous devez d'abord accepter le pret avant de l'exporter"]);
                exit;
            }
            
            // Récup info type prêt et EF
            $db = getDB();
            $typePret = $db->prepare('SELECT * FROM type_pret WHERE id = ?');
            $typePret->execute([$pret['id_type_pret']]);
            $typePret = $typePret->fetch(PDO::FETCH_ASSOC);
            if (!$typePret) {
                http_response_code(500);
                echo json_encode(['error' => 'Type de prêt introuvable']);
                exit;
            }
            
            $ef = $db->prepare('SELECT * FROM etablissement_financier WHERE id = ?');
            $ef->execute([$pret['id_ef']]);
            $ef = $ef->fetch(PDO::FETCH_ASSOC);
            if (!$ef) {
                http_response_code(500);
                echo json_encode(['error' => 'Établissement financier introuvable']);
                exit;
            }
            
            $etudiant = $db->prepare('SELECT * FROM etudiant WHERE id = ?');
            $etudiant->execute([$pret['id_etudiant']]);
            $etudiant = $etudiant->fetch(PDO::FETCH_ASSOC);
            if (!$etudiant) {
                http_response_code(500);
                echo json_encode(['error' => 'Étudiant introuvable']);
                exit;
            }
            
            // Tableau d'amortissement
            $tableau = Pret::genererTableauAmortissement(
                $pret['montant'],
                $typePret['taux'],
                $pret['duree_mois'], // utiliser la durée réelle du prêt
                $pret['date_pret'],
                $pret['assurance'],
                $pret['delai_premier_remboursement']
            );
            
            // Génération PDF
            $pdf = new \FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial','B',16);
            // Titre souligné sans accent
            $pdf->SetTextColor(0,0,128);
            $pdf->Cell(0,10,'Contrat de pret',0,1,'C');
            $pdf->SetDrawColor(0,0,128);
            $pdf->SetLineWidth(0.7);
            $pdf->Line(10, 20, 200, 20);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFont('Arial','',12);
            $pdf->Cell(0,10,'Etablissement : '.$ef['nom'],0,1);
            $pdf->Cell(0,10,'Client : '.$etudiant['nom'].' '.$etudiant['prenom'],0,1);
            $pdf->Cell(0,10,'Type de pret : '.$typePret['nom'],0,1);
            $pdf->Cell(0,10,'Montant : '.number_format($pret['montant'],2).' EUR',0,1);
            $pdf->Cell(0,10,'Taux : '.$typePret['taux'].' %',0,1);
            $pdf->Cell(0,10,'Assurance : '.$pret['assurance'].' %',0,1);
            $pdf->Cell(0,10,'Date de debut : '.$pret['date_pret'],0,1);
            $pdf->Cell(0,10,'Delai 1er remboursement : '.$pret['delai_premier_remboursement'].' mois',0,1);
            $pdf->Cell(0,10,'',0,1);
            $pdf->SetFont('Arial','B',12);
            $pdf->SetTextColor(255,255,255);
            $pdf->SetFillColor(0,0,128);
            $pdf->Cell(0,10,'Tableau d\'amortissement',0,1,'L',true);
            $pdf->SetFont('Arial','',10);
            $pdf->SetTextColor(0,0,0);
            // En-tête du tableau
            $pdf->SetFillColor(200,200,255);
            $pdf->Cell(20,8,'#',1,0,'C',true);
            $pdf->Cell(30,8,'Date',1,0,'C',true);
            $pdf->Cell(30,8,'Annuite',1,0,'C',true);
            $pdf->Cell(30,8,'Amort.',1,0,'C',true);
            $pdf->Cell(30,8,'Interets',1,0,'C',true);
            $pdf->Cell(30,8,'Assurance',1,0,'C',true);
            $pdf->Cell(30,8,'Capital restant',1,1,'C',true);
            // Lignes du tableau avec alternance de couleur
            $fill = false;
            foreach ($tableau as $row) {
                $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, 255);
                $pdf->Cell(20,8,$row['echeance'],1,0,'C',true);
                $pdf->Cell(30,8,$row['date'],1,0,'C',true);
                $pdf->Cell(30,8,number_format($row['annuite'],2),1,0,'C',true);
                $pdf->Cell(30,8,number_format($row['amortissement'],2),1,0,'C',true);
                $pdf->Cell(30,8,number_format($row['interets'],2),1,0,'C',true);
                $pdf->Cell(30,8,number_format($row['assurance'],2),1,0,'C',true);
                $pdf->Cell(30,8,number_format($row['capital_restant'],2),1,1,'C',true);
                $fill = !$fill;
            }
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="pret_'.$id.'.pdf"');
            $pdf->Output('I','pret_'.$id.'.pdf');
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
        }
        exit;
    }
} 