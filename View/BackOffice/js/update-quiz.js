document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("quizForm");

    const titre = document.getElementById("titre");
    const description = document.getElementById("description");
    const image = document.getElementById("image");

    const titreError = document.getElementById("titreError");
    const descriptionError = document.getElementById("descriptionError");
    const imageError = document.getElementById("imageError");

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
            setError(titre, titreError, "Minimum 3 caractères.");
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
            setError(description, descriptionError, "Minimum 10 caractères.");
            return false;
        }

        setSuccess(description, descriptionError);
        return true;
    }

    // 🔴 IMAGE OPTIONNELLE (différence avec create)
    function validateImage() {

        if (image.files.length === 0) {
            // pas d'image → OK
            setSuccess(image, imageError);
            return true;
        }

        const file = image.files[0];
        const fileName = file.name;

        if (!imagePattern.test(fileName)) {
            setError(image, imageError, "Formats autorisés : jpg, jpeg, png, webp.");
            return false;
        }

        setSuccess(image, imageError);
        return true;
    }

    titre.addEventListener("input", validateTitre);
    description.addEventListener("input", validateDescription);
    image.addEventListener("change", validateImage);

    form.addEventListener("submit", function (e) {

        const t = validateTitre();
        const d = validateDescription();
        const i = validateImage();

        if (!t || !d || !i) {
            e.preventDefault();
        }
    });
    console.log("j'ai bien resussir ");

});