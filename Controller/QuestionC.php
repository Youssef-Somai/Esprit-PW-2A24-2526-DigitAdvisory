<?php
require_once __DIR__ . '/../config.php';

class QuestionC
{
    public function addQuestion($question)
    {
        $sql = "INSERT INTO question 
                (id_quiz, question, choix1, choix2, choix3, choix4, bonne_reponse)
                VALUES 
                (:id_quiz, :question, :choix1, :choix2, :choix3, :choix4, :bonne_reponse)";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_quiz' => $question->getIdQuiz(),
                'question' => $question->getQuestion(),
                'choix1' => $question->getChoix1(),
                'choix2' => $question->getChoix2(),
                'choix3' => $question->getChoix3(),
                'choix4' => $question->getChoix4(),
                'bonne_reponse' => $question->getBonneReponse()
            ]);
        } catch (Exception $e) {
            die('Error addQuestion: ' . $e->getMessage());
        }
    }

    public function listQuestions()
    {
        $sql = "SELECT * FROM question ORDER BY id_question DESC";
        $db = config::getConnexion();

        try {
            return $db->query($sql);
        } catch (Exception $e) {
            die('Error listQuestions: ' . $e->getMessage());
        }
    }

    public function listQuestionsByQuiz($id_quiz)
    {
        $sql = "SELECT * FROM question WHERE id_quiz = :id_quiz ORDER BY id_question DESC";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_quiz' => $id_quiz
            ]);
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Error listQuestionsByQuiz: ' . $e->getMessage());
        }
    }

    public function showQuestion($id)
    {
        $sql = "SELECT * FROM question WHERE id_question = :id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id
            ]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Error showQuestion: ' . $e->getMessage());
        }
    }

    public function updateQuestion($question, $id)
    {
        $sql = "UPDATE question SET
                    id_quiz = :id_quiz,
                    question = :question,
                    choix1 = :choix1,
                    choix2 = :choix2,
                    choix3 = :choix3,
                    choix4 = :choix4,
                    bonne_reponse = :bonne_reponse
                WHERE id_question = :id";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id,
                'id_quiz' => $question->getIdQuiz(),
                'question' => $question->getQuestion(),
                'choix1' => $question->getChoix1(),
                'choix2' => $question->getChoix2(),
                'choix3' => $question->getChoix3(),
                'choix4' => $question->getChoix4(),
                'bonne_reponse' => $question->getBonneReponse()
            ]);
        } catch (Exception $e) {
            die('Error updateQuestion: ' . $e->getMessage());
        }
    }

    public function deleteQuestion($id)
    {
        $sql = "DELETE FROM question WHERE id_question = :id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id
            ]);
        } catch (Exception $e) {
            die('Error deleteQuestion: ' . $e->getMessage());
        }
    }
}