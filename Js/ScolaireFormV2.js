let formScolaire = document.getElementById("scolaire");
let autreClient = document.getElementById("compoClient");
let selectGroupe = document.getElementById("typeGroupe");

selectGroupe.addEventListener("input", changingForm);
// On compare le type de groupe du client aux valeur des options. Si ils correspondent, on définit le champ correspondant comme étant le champ sélectionné.
// Puis on appelle changingForm() pour afficher les champs number.
let typeGroupes = document.querySelectorAll('#typeGroupe > option');
for(option of typeGroupes)
{
    if(option.value == document.getElementById('valueTypeGroupe').textContent)
    {
        option.setAttribute("selected", "");
        changingForm();
    }
}

function changingForm() {
    formScolaire.innerHTML = "";
    autreClient.innerHTML = "";
    if(selectGroupe.value == "scolaire") {
        let htmlForm = '<div class="form-group row">';
        htmlForm += '<label for="nivScolaire" class="col-lg-2 col-sm-4 col-form-label">Niveau</label>';
        htmlForm += '<div class="col-lg-4 col-sm-8">';
        htmlForm += '<select id="nivScolaire" name="TYPE_SCOLAIRE" class="form-control">';
        htmlForm += '<option value="primaire">Primaire (de CP à CM2)</option>';
        htmlForm += '<option value="collège">Collège</option>';
        htmlForm += '<option value="lycée">Lycée</option>';
        htmlForm += '</select></div>';
        htmlForm += '</div>';
        htmlForm += '<div class="form-group row">';
        htmlForm += '<label for="nbEnfants" class="col-lg-2 col-sm-4 col-form-label">Nombre d\'élèves</label>';
        htmlForm += '<div class="col-lg-4 col-sm-8">';
        htmlForm += '<input type="number" id="nbEnfants" name="NB_ENFANTS" placeholder="0" min="0" class="form-control" required>';
        htmlForm += '</div>';
        htmlForm += '</div>';
        htmlForm += '<div class="form-group row">';
        htmlForm += '<label for="nbEnfants" class="col-lg-2 col-sm-4 col-form-label">Nombre d\'accompagnateurs</label>';
        htmlForm += '<div class="col-lg-4 col-sm-8 mt-2">';
        htmlForm += '<input type="number" id="nbAdultes" name="NB_ADULTES" placeholder="0" min="0" class="form-control" required>';
        htmlForm += '</div>';
        htmlForm += '</div>';
        htmlForm += '<div class="form-group row mb-5">';
        htmlForm += '<label for="nbTotal" class="col-lg-2 col-sm-4 col-form-label">Taille du groupe</label>';
        htmlForm += '<div class="col-lg-4 col-sm-8">';
        htmlForm += '<input type="number" name="TAILLE_GROUPE" id="nbTotal" placeholder="0" class="form-control" readonly required>';
        htmlForm += '</div>';
        htmlForm += '</div>';

        formScolaire.innerHTML = htmlForm;
        
        //console.log("scolaire");
    }
    else {
        let htmlForm = '<div class="form-group row">';
        htmlForm += '<label for="nbEnfants" class="col-lg-2 col-sm-4 col-form-label">Enfants de moins de 12 ans</label>';
        htmlForm += '<div class="col-lg-4 col-sm-8 mt-2">';
        htmlForm += '<input type="number" placeholder="Nombre d\'enfants" id="nbEnfants" name="NB_ENFANTS" placeholder="0" min="0" class="form-control" required>';
        htmlForm += '</div>';
        htmlForm += '</div>';
        htmlForm += '<div class="form-group row">';
        htmlForm += '<label for="nbAdos" class="col-lg-2 col-sm-4 col-form-label">Adolescents (de 13 à 17 ans)</label>';
        htmlForm += '<div class="col-lg-4 col-sm-8">';
        htmlForm += '<input type="number" placeholder="Nombre d\'adolescents" id="nbAdos" name="NB_ADOS" placeholder="0" min="0" class="form-control" required>';
        htmlForm += '</div>';
        htmlForm += '</div>';
        htmlForm += '<div class="form-group row">';
        htmlForm += '<label for="nbAdultes" class="col-lg-2 col-sm-4 col-form-label">Adultes</label>';
        htmlForm += '<div class="col-lg-4 col-sm-8">';
        htmlForm += '<input type="number" placeholder="Nombre d\'adultes" id="nbAdultes" name="NB_ADULTES" placeholder="0" min="0" class="form-control" required>';
        htmlForm += '</div>';
        htmlForm += '</div>';
        htmlForm += '<div class="form-group row mb-5">';
        htmlForm += '<label for="nbTotal" class="col-lg-2 col-sm-4 col-form-label">Taille du groupe</label>';
        htmlForm += '<div class="col-lg-4 col-sm-8">';
        htmlForm += '<input type="number" name="TAILLE_GROUPE" id="nbTotal" placeholder="0" class="form-control" readonly required>';
        htmlForm += '</div>';
        htmlForm += '</div>';

        compoClient.innerHTML = htmlForm;

        //console.log("autre");
    }
}