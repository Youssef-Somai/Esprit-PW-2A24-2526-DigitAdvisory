document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("quizForm");

    const titre = document.getElementById("titre");
    const description = document.getElementById("description");
    const image = document.getElementById("image");

    const titreError = document.getElementById("titreError");
    const descriptionError = document.getElementById("descriptionError");
    const imageError = document.getElementById("imageError");

    const imageTrigger = document.getElementById("imageTrigger");
    const imageName = document.getElementById("imageName");

    // accepte jpg, jpeg, png, webp et refuse gif
    const imagePattern = /\.(jpg|jpeg|png|webp)$/i;

    function setError(input, errorElement, message) {
        input.classList.add("input-error");
        input.classList.remove("input-success");
        errorElement.textContent = message;
    }

    function setSuccess(input, errorElement) {
        input.classList.remove("input-error");
        input.classList.add("input-success");
        errorElement.textContent = "";
    }

    function cleanValue(value) {
        return value.trim().replace(/\s+/g, " ");
    }

    function validateTitre() {
        const value = cleanValue(titre.value);

        if (value === "") {
            setError(titre, titreError, "Le titre est obligatoire.");
            return false;
        }

        if (value.length < 3) {
            setError(titre, titreError, "Le titre doit contenir au moins 3 caractères.");
            return false;
        }

        if (value.length > 255) {
            setError(titre, titreError, "Le titre ne doit pas dépasser 255 caractères.");
            return false;
        }

        const titrePattern = /^[A-Za-zÀ-ÿ0-9\s'’\-_,.()]+$/;
        if (!titrePattern.test(value)) {
            setError(titre, titreError, "Le titre contient des caractères non autorisés.");
            return false;
        }

        setSuccess(titre, titreError);
        return true;
    }

    function validateDescription() {
        const value = cleanValue(description.value);

        if (value === "") {
            setError(description, descriptionError, "La description est obligatoire.");
            return false;
        }

        if (value.length < 10) {
            setError(description, descriptionError, "La description doit contenir au moins 10 caractères.");
            return false;
        }

        if (value.length > 1000) {
            setError(description, descriptionError, "La description ne doit pas dépasser 1000 caractères.");
            return false;
        }

        setSuccess(description, descriptionError);
        return true;
    }
    function validateImage() {
   
    if (!image.files || image.files.length === 0) {
        setSuccess(image, imageError);
        return true;
    }

    const file = image.files[0];
    const fileName = file.name.trim();

    if (fileName.length > 255) {
        setError(image, imageError, "Le nom de l'image est trop long.");
        return false;
    }

    if (!imagePattern.test(fileName)) {
        setError(image, imageError, "Format invalide. Formats autorisés : jpg, jpeg, png, webp.");
        return false;
    }

    // refuse explicitement gif
    if (/\.gif$/i.test(fileName)) {
        setError(image, imageError, "Le format GIF n'est pas autorisé.");
        return false;
    }

    setSuccess(image, imageError);
    return true;
}

    titre.addEventListener("input", validateTitre);
    titre.addEventListener("blur", validateTitre);

    description.addEventListener("input", validateDescription);
    description.addEventListener("blur", validateDescription);

    image.addEventListener("change", function () {
        if (image.files.length > 0) {
            imageName.value = image.files[0].name;
        } else {
            imageName.value = "";
        }

        validateImage();
    });

    form.addEventListener("submit", function (e) {
        const isTitreValid = validateTitre();
        const isDescriptionValid = validateDescription();
        const isImageValid = validateImage();

        if (!isTitreValid || !isDescriptionValid || !isImageValid) {
            e.preventDefault();
        }
    });

    if (imageTrigger && image) {
        imageTrigger.addEventListener("click", function () {
            image.click();
        });
    }

    console.log("je suis bien connecté");
});