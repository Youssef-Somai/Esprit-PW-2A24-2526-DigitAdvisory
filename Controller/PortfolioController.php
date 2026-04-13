<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Portfolio.php';

class PortfolioController {
    public function listPortfolios() {
        $db = config::getConnexion();
        $sql = "SELECT * FROM portfolio";
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function deletePortfolio($id) {
        $db = config::getConnexion();
        $sql = "DELETE FROM portfolio WHERE id_portfolio = :id";
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':id', $id);
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addPortfolio($portfolio) {
        $db = config::getConnexion();
        $sql = "INSERT INTO portfolio (titre_portfolio, description_portfolio) VALUES (:titre, :description)";
        try {
            $req = $db->prepare($sql);
            $req->execute([
                'titre' => $portfolio->getTitrePortfolio(),
                'description' => $portfolio->getDescriptionPortfolio()
            ]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function showPortfolio($id) {
        $db = config::getConnexion();
        $sql = "SELECT * FROM portfolio WHERE id_portfolio = :id";
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':id', $id);
            $req->execute();
            return $req->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function updatePortfolio($portfolio, $id) {
        $db = config::getConnexion();
        $sql = "UPDATE portfolio SET titre_portfolio = :titre, description_portfolio = :description WHERE id_portfolio = :id";
        try {
            $req = $db->prepare($sql);
            $req->execute([
                'titre' => $portfolio->getTitrePortfolio(),
                'description' => $portfolio->getDescriptionPortfolio(),
                'id' => $id
            ]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>
