<?php
require_once __DIR__ . '/../db.php';

class Etudiant {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    public function getAll() {
        try {
            $stmt = $this->db->query("SELECT * FROM etudiant ORDER BY nom, prenom");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des étudiants: " . $e->getMessage());
        }
    }
    
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM etudiant WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération de l'étudiant: " . $e->getMessage());
        }
    }
    
    public function create($data) {
        try {
            $stmt = $this->db->prepare("INSERT INTO etudiant (nom, prenom, email, age) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                trim($data['nom']),
                trim($data['prenom']),
                trim($data['email']),
                intval($data['age'])
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                throw new Exception("Un étudiant avec cet email existe déjà");
            }
            throw new Exception("Erreur lors de la création de l'étudiant: " . $e->getMessage());
        }
    }
    
    public function update($id, $data) {
        try {
            $stmt = $this->db->prepare("UPDATE etudiant SET nom = ?, prenom = ?, email = ?, age = ? WHERE id = ?");
            $stmt->execute([
                trim($data['nom']),
                trim($data['prenom']),
                trim($data['email']),
                intval($data['age']),
                $id
            ]);
            
            if ($stmt->rowCount() === 0) {
                throw new Exception("Aucune modification effectuée");
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                throw new Exception("Un étudiant avec cet email existe déjà");
            }
            throw new Exception("Erreur lors de la modification de l'étudiant: " . $e->getMessage());
        }
    }
    
    public function delete($id) {
        try {
            // Vérifier s'il y a des prêts liés
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM pret WHERE id_etudiant = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Impossible de supprimer cet étudiant car il a des prêts associés");
            }
            
            $stmt = $this->db->prepare("DELETE FROM etudiant WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                throw new Exception("Aucune suppression effectuée");
            }
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression de l'étudiant: " . $e->getMessage());
        }
    }
}
?> 