<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/ElementPortfolio.php';

class ElementPortfolioController {
    public function listElements($id_portfolio) {
        $db = config::getConnexion();
        $sql = "SELECT * FROM element_portfolio WHERE id_portfolio = :id_portfolio ORDER BY date_ajout DESC";
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':id_portfolio', $id_portfolio);
            $req->execute();
            return $req->fetchAll();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function listAllElements() {
        $db = config::getConnexion();
        $sql = "SELECT e.*, p.titre_portfolio FROM element_portfolio e JOIN portfolio p ON e.id_portfolio = p.id_portfolio ORDER BY e.date_ajout DESC";
        try {
            $req = $db->query($sql);
            return $req->fetchAll();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addElement($element) {
        $db = config::getConnexion();
        $sql = "INSERT INTO element_portfolio (id_portfolio, type_element, titre, description, niveau, statut) VALUES (:id_portfolio, :type_element, :titre, :description, :niveau, :statut)";
        try {
            $req = $db->prepare($sql);
            $req->execute([
                'id_portfolio' => $element->getIdPortfolio(),
                'type_element' => $element->getTypeElement(),
                'titre' => $element->getTitre(),
                'description' => $element->getDescription(),
                'niveau' => $element->getNiveau(),
                'statut' => $element->getStatut()
            ]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function deleteElement($id) {
        $db = config::getConnexion();
        $sql = "DELETE FROM element_portfolio WHERE id_element = :id";
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':id', $id);
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function showElement($id) {
        $db = config::getConnexion();
        $sql = "SELECT * FROM element_portfolio WHERE id_element = :id";
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':id', $id);
            $req->execute();
            return $req->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function updateElement($element, $id) {
        $db = config::getConnexion();
        $sql = "UPDATE element_portfolio SET type_element = :type_element, titre = :titre, description = :description, niveau = :niveau, statut = :statut WHERE id_element = :id";
        try {
            $req = $db->prepare($sql);
            $req->execute([
                'type_element' => $element->getTypeElement(),
                'titre' => $element->getTitre(),
                'description' => $element->getDescription(),
                'niveau' => $element->getNiveau(),
                'statut' => $element->getStatut(),
                'id' => $id
            ]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
    
    public function searchElements($id_portfolio, $keyword, $type = '') {
        $db = config::getConnexion();
        $sql = "SELECT * FROM element_portfolio WHERE id_portfolio = :id_portfolio AND titre LIKE :keyword";
        if (!empty($type)) {
            $sql .= " AND type_element = :type";
        }
        
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':id_portfolio', $id_portfolio);
            $req->bindValue(':keyword', '%' . $keyword . '%');
            if (!empty($type)) {
                $req->bindValue(':type', $type);
            }
            $req->execute();
            return $req->fetchAll();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>
