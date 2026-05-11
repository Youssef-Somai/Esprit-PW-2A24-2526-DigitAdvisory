<?php

class Question
{
    private $id_question;
    private $id_quiz;
    private $question;
    private $choix1;
    private $choix2;
    private $choix3;
    private $choix4;
    private $bonne_reponse;

    public function __construct(
        $id_question,
        $id_quiz,
        $question,
        $choix1,
        $choix2,
        $choix3,
        $choix4,
        $bonne_reponse
    ) {
        $this->id_question = $id_question;
        $this->id_quiz = $id_quiz;
        $this->question = $question;
        $this->choix1 = $choix1;
        $this->choix2 = $choix2;
        $this->choix3 = $choix3;
        $this->choix4 = $choix4;
        $this->bonne_reponse = $bonne_reponse;
    }

    public function getIdQuestion()
    {
        return $this->id_question;
    }

    public function getIdQuiz()
    {
        return $this->id_quiz;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    public function getChoix1()
    {
        return $this->choix1;
    }

    public function getChoix2()
    {
        return $this->choix2;
    }

    public function getChoix3()
    {
        return $this->choix3;
    }

    public function getChoix4()
    {
        return $this->choix4;
    }

    public function getBonneReponse()
    {
        return $this->bonne_reponse;
    }

    public function setIdQuestion($id_question)
    {
        $this->id_question = $id_question;
    }

    public function setIdQuiz($id_quiz)
    {
        $this->id_quiz = $id_quiz;
    }

    public function setQuestion($question)
    {
        $this->question = $question;
    }

    public function setChoix1($choix1)
    {
        $this->choix1 = $choix1;
    }

    public function setChoix2($choix2)
    {
        $this->choix2 = $choix2;
    }

    public function setChoix3($choix3)
    {
        $this->choix3 = $choix3;
    }

    public function setChoix4($choix4)
    {
        $this->choix4 = $choix4;
    }

    public function setBonneReponse($bonne_reponse)
    {
        $this->bonne_reponse = $bonne_reponse;
    }
}