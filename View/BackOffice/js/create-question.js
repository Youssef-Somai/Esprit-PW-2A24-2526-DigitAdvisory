document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("questionForm");

    const question = document.getElementById("question");
    const choix1 = document.getElementById("choix1");
    const choix2 = document.getElementById("choix2");
    const choix3 = document.getElementById("choix3");
    const choix4 = document.getElementById("choix4");
    const bonneReponse = document.getElementById("bonne_reponse");

    const questionError = document.getElementById("questionError");
    const choix1Error = document.getElementById("choix1Error");
    const choix2Error = document.getElementById("choix2Error");
    const choix3Error = document.getElementById("choix3Error");
    const choix4Error = document.getElementById("choix4Error");
    const bonneReponseError = document.getElementById("bonneReponseError");

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

    function validateQuestion() {
        const value = cleanValue(question.value);

        if (value === "") {
            setError(question, questionError, "La question est obligatoire.");
            return false;
        }

        if (value.length < 10) {
            setError(question, questionError, "La question doit contenir au moins 10 caractères.");
            return false;
        }

        if (value.length > 1000) {
            setError(question, questionError, "La question ne doit pas dépasser 1000 caractères.");
            return false;
        }

        if (!/^[A-ZÀ-Ÿ]/u.test(value)) {
            setError(question, questionError, "La question doit commencer par une majuscule.");
            return false;
        }

        if (!/[?.]$/.test(value)) {
            setError(question, questionError, "La question doit se terminer par ? ou .");
            return false;
        }

        setSuccess(question, questionError);
        return true;
    }

    function validateChoix(input, errorElement, label) {
        const value = cleanValue(input.value);

        if (value === "") {
            setError(input, errorElement, label + " est obligatoire.");
            return false;
        }

        if (value.length < 1) {
            setError(input, errorElement, label + " est invalide.");
            return false;
        }

        if (value.length > 255) {
            setError(input, errorElement, label + " ne doit pas dépasser 255 caractères.");
            return false;
        }

        setSuccess(input, errorElement);
        return true;
    }

    function validateBonneReponse() {
        const value = bonneReponse.value;

        if (value === "") {
            setError(bonneReponse, bonneReponseError, "Veuillez choisir la bonne réponse.");
            return false;
        }

        if (!["1", "2", "3", "4"].includes(value)) {
            setError(bonneReponse, bonneReponseError, "La bonne réponse doit être 1, 2, 3 ou 4.");
            return false;
        }

        setSuccess(bonneReponse, bonneReponseError);
        return true;
    }

    function validateChoixDifferents() {
        const v1 = cleanValue(choix1.value).toLowerCase();
        const v2 = cleanValue(choix2.value).toLowerCase();
        const v3 = cleanValue(choix3.value).toLowerCase();
        const v4 = cleanValue(choix4.value).toLowerCase();

        const values = [v1, v2, v3, v4].filter(v => v !== "");
        const uniqueValues = new Set(values);

        if (values.length !== uniqueValues.size) {
            setError(choix1, choix1Error, "Les choix doivent être différents.");
            setError(choix2, choix2Error, "Les choix doivent être différents.");
            setError(choix3, choix3Error, "Les choix doivent être différents.");
            setError(choix4, choix4Error, "Les choix doivent être différents.");
            return false;
        }

        validateChoix(choix1, choix1Error, "Le choix 1");
        validateChoix(choix2, choix2Error, "Le choix 2");
        validateChoix(choix3, choix3Error, "Le choix 3");
        validateChoix(choix4, choix4Error, "Le choix 4");
        return true;
    }

    question.addEventListener("input", validateQuestion);
    question.addEventListener("blur", validateQuestion);

    choix1.addEventListener("input", function () {
        validateChoix(choix1, choix1Error, "Le choix 1");
        validateChoixDifferents();
    });
    choix1.addEventListener("blur", function () {
        validateChoix(choix1, choix1Error, "Le choix 1");
        validateChoixDifferents();
    });

    choix2.addEventListener("input", function () {
        validateChoix(choix2, choix2Error, "Le choix 2");
        validateChoixDifferents();
    });
    choix2.addEventListener("blur", function () {
        validateChoix(choix2, choix2Error, "Le choix 2");
        validateChoixDifferents();
    });

    choix3.addEventListener("input", function () {
        validateChoix(choix3, choix3Error, "Le choix 3");
        validateChoixDifferents();
    });
    choix3.addEventListener("blur", function () {
        validateChoix(choix3, choix3Error, "Le choix 3");
        validateChoixDifferents();
    });

    choix4.addEventListener("input", function () {
        validateChoix(choix4, choix4Error, "Le choix 4");
        validateChoixDifferents();
    });
    choix4.addEventListener("blur", function () {
        validateChoix(choix4, choix4Error, "Le choix 4");
        validateChoixDifferents();
    });

    bonneReponse.addEventListener("change", validateBonneReponse);
    bonneReponse.addEventListener("blur", validateBonneReponse);

    form.addEventListener("submit", function (e) {
        const q = validateQuestion();
        const c1 = validateChoix(choix1, choix1Error, "Le choix 1");
        const c2 = validateChoix(choix2, choix2Error, "Le choix 2");
        const c3 = validateChoix(choix3, choix3Error, "Le choix 3");
        const c4 = validateChoix(choix4, choix4Error, "Le choix 4");
        const br = validateBonneReponse();
        const diff = validateChoixDifferents();

        if (!q || !c1 || !c2 || !c3 || !c4 || !br || !diff) {
            e.preventDefault();
        }
    });

    console.log("create-question.js bien connecté");
});