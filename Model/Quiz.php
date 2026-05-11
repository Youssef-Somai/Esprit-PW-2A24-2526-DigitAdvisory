<?php

class Quiz
{
    
    private $id_quiz;
    private $titre;
    private $description;
    private $image;
    private $date_creation;

    
    public function __construct($id_quiz, $titre, $description, $image, $date_creation)
    {
        $this->id_quiz = $id_quiz;
        $this->titre = $titre;
        $this->description = $description;
        $this->image = $image;
        $this->date_creation = $date_creation;
    }



    public function getIdQuiz()
    {
        return $this->id_quiz;
    }

    public function getTitre()
    {
        return $this->titre;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getDateCreation()
    {
        return $this->date_creation;
    }

   

    public function setIdQuiz($id_quiz)
    {
        $this->id_quiz = $id_quiz;
    }

    public function setTitre($titre)
    {
        $this->titre = $titre;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function setDateCreation($date_creation)
    {
        $this->date_creation = $date_creation;
    }
}