<?php

require_once __DIR__ . '/../config.php';

class QuizC
{
    // Ajouter un quiz
    public function addQuiz($quiz)
    {
        $sql = "INSERT INTO quiz (titre, description, image, date_creation) 
                VALUES (:titre, :description, :image, :date_creation)";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre' => $quiz->getTitre(),
                'description' => $quiz->getDescription(),
                'image' => $quiz->getImage(),
                'date_creation' => $quiz->getDateCreation()
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    // Afficher tous les quiz
    public function listQuiz()
    {
        $sql = "SELECT * FROM quiz ORDER BY id_quiz DESC";
        $db = config::getConnexion();

        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Afficher un quiz par ID
    public function showQuiz($id)
    {
        $sql = "SELECT * FROM quiz WHERE id_quiz = :id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id
            ]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Modifier un quiz
    public function updateQuiz($quiz, $id)
    {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                "UPDATE quiz SET
                    titre = :titre,
                    description = :description,
                    image = :image,
                    date_creation = :date_creation
                WHERE id_quiz = :id"
            );

            $query->execute([
                'id' => $id,
                'titre' => $quiz->getTitre(),
                'description' => $quiz->getDescription(),
                'image' => $quiz->getImage(),
                'date_creation' => $quiz->getDateCreation()
            ]);

            echo $query->rowCount() . " records UPDATED successfully <br>";
        } catch (PDOException $e) {
            $e->getMessage();
        }
    }

    // Supprimer un quiz
    public function deleteQuiz($id)
    {
        $sql = "DELETE FROM quiz WHERE id_quiz = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);

        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>