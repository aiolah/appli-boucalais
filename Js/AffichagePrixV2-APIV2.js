/* --------------------------------------------- RÉCUPÉRATION DES PRIX DES SÉJOURS & OPTIONS AVEC AJAX ----------------------------------------------------- */

let prixSejours = ['1er item du tableau créé dans le but que les id des prix soient égaux à leur key (du tableau). Les tableaux en javascript commencent en effet par la key 0..'];
let fetchOptions = { method: 'GET' };
fetch('http://leboucalais.fr/application/API/prixSejours.php', fetchOptions)
.then( (response) => { return response.json() } )
.then( (dataJSON) => {
    dataJSON.forEach( (prixSejour) => {
        prixSejours.push({
            id: prixSejour['ID_SEJOUR'],
            prix: parseFloat(prixSejour['PRIX_SEJOUR_UNITE'], 10)
        });
    });
});

let prixOptions = [0];
fetch('http://leboucalais.fr/application/API/prixOptions.php', fetchOptions)
.then( (response) => { return response.json() } )
.then( (dataJSON) => {
    dataJSON.forEach( (prixOption) => {
        prixOptions.push({
            id: prixOption['ID_OPTION'],
            prix: parseFloat(prixOption['PRIX_OPTION_UNITE'], 10)
        });
    });
});

/* -------------------------------------------------------------- CALCUL TAILLE DU GROUPE --------------------------------------------------------------- */

// On met les écouteurs d'évènements sur les champs input dès l'exécution du script (car le type de groupe est préselectionné)
setListeners();
// LISTENER SUR LE CHAMP INPUT typeGroupe
document.getElementById('typeGroupe').addEventListener('change', setListeners);

/**
 * Positionne des écouteurs d'évènements sur les 2 (ou 3) champs input
 */
function setListeners()
{
    if(document.getElementById('nivScolaire') != null)
    {
        document.getElementById('nivScolaire').addEventListener('change', ready);
    }
    document.getElementById('nbEnfants').addEventListener('input', calculTailleGroupe);
    document.getElementById('nbAdultes').addEventListener('input', calculTailleGroupe);
    if(document.getElementById('compoClient').textContent != "")
    {
        document.getElementById('nbAdos').addEventListener('input', calculTailleGroupe); 
    }
}

/**
 * Calcule la taille du groupe si les champs ne sont pas vides
 */
function calculTailleGroupe()
{
    if(document.getElementById('scolaire').textContent != "" && document.getElementById('nbEnfants').value != "" && document.getElementById('nbAdultes').value != "")
    {
        let tailleGroupe = parseInt(document.getElementById('nbEnfants').value) + parseInt(document.getElementById('nbAdultes').value);
        document.getElementById('nbTotal').value = tailleGroupe;
        ready();
    }
    else if(document.getElementById('compoClient').textContent != "" && document.getElementById('nbEnfants').value != "" && document.getElementById('nbAdultes').value != "" && document.getElementById('nbAdos').value != "")
    {
        let tailleGroupe = parseInt(document.getElementById('nbEnfants').value) + parseInt(document.getElementById('nbAdultes').value) + parseInt(document.getElementById('nbAdos').value);
        document.getElementById('nbTotal').value = tailleGroupe;
        ready();
    }
}

/* -------------------------------------------------------------- CALCUL PRIX HEBERGEMENT ----------------------------------------------------------------- */

let prixHebergement = 0;
let prixFraisActivites = 0;
let prixFraisOptionnels = 0;
let prixTotal = 0;
let divPrixHebergement = document.getElementById('prixHebergement');
let divPrixActivites = document.getElementById('prixActivites');
let divPrixFraisOptionnels = document.getElementById('prixFraisOptionnels');
let divPrixTotal = document.getElementById('prixTotal');
let champPrixHebergement = document.querySelector("[name=PRIX_HEBERGEMENT]");
let champPrixActivites = document.querySelector("[name=PRIX_ACTIVITES]");
let champPrixFraisOptionnels = document.querySelector("[name=PRIX_FRAIS_OPTIONNELS]");
let champPrixTotal = document.querySelector("[name=PRIX_TOTAL]");

let dateDebut = document.getElementById('dateDebutReserv');
let dateFin = document.getElementById('dateFinReserv');

// Détermination de la date minimum pour les champs input → date d'aujourd'hui
let today = new Date();
let month = today.getMonth() + 1;
if(month.toString().length == 1)
{
    month = "0" + month;
}
today = `${today.getFullYear()}-${month}-${today.getDate()}`;
dateDebut.setAttribute('min', today);
dateFin.setAttribute('min', today);

// LISTENERS
dateDebut.addEventListener("input", function(e) { controleDates(e, dateDebut, dateFin) });
dateFin.addEventListener("input", function(e) { controleDates(e, dateDebut, dateFin) });
// Listeners sur les boutons radios
for(radio of document.querySelectorAll('[name=TYPE_PENSION]'))
{
    radio.addEventListener("change", ready);
}

/**
 * Fonction de callback du listener sur les dates, détermine la saison du séjour en fonction des dates sélectionnées
 * @param {DOM Element} e champs input dateDebut ou dateFin (élément déclencheur de l'évènement)
 * @param {DOM Element} dateDebut champs input dateDebut
 * @param {DOM Element} dateFin champs input DateFin
 */
function controleDates(e, dateDebut, dateFin)
{
    // Détermination de la saison en fonction du mois sélectionné
    let divMsg = document.getElementById("msg-" + e.target.id);
    let date = new Date(e.target.value);
    let mois = date.getMonth() + 1;
    // Basse saison : mars-juin, septembre-novembre
    if((mois >= 3 && mois <= 6) || (mois >= 9 && mois <= 11))
    {
        divMsg.textContent = "";
        document.getElementById(e.target.id).classList.remove('invalid-input');
        if(getNbreNuits(dateDebut, dateFin))
        {
            document.getElementById("saison").value = "basse";
            afficherHauteSaison("basse");
            ready();
        }
    }
    // Haute saison : juillet-août
    else if(mois == 7 || mois == 8)
    {
        if(document.getElementById('typeGroupe').value == "scolaire")
        {
            divMsg.textContent = 'Les groupes scolaires ne peuvent pas réserver en haute saison... Vous êtes en vacances non 😜 ?';
        }
        else
        {
            divMsg.textContent = "";
            document.getElementById(e.target.id).classList.remove('invalid-input');
            if(getNbreNuits(dateDebut, dateFin))
            {
                document.getElementById("saison").value = "haute";
                afficherHauteSaison("haute");
                ready();
            }
        }
    }
    // Hors saison
    else if((mois >= 1 && mois <= 2) || mois == 12)
    {
        divMsg.textContent = 'Le centre est fermé à cette période, veuillez choisir un autre mois.';
        document.getElementById(e.target.id).classList.add('invalid-input');
    }
}

// FONCTIONS

/**
 * Calcule le nombre de nuits et l'affiche s'il n'est pas négatif
 * @returns false ou true
 */ 
function getNbreNuits()
{
    // Calcul du nombre de nuits si les conditions sont bonnes
    if(dateDebut.value != "" && dateFin.value != "" && dateDebut.value.substring(0, 1) != 0 && dateFin.value.substring(0, 1) != 0)
    {
        let divDate = document.getElementById("date-" + dateFin.id);
        let dDateDebut = new Date(dateDebut.value);
        let dDateFin = new Date(dateFin.value);
        let nbreNuits = (dDateFin - dDateDebut)/1000/3600/24;
        if(nbreNuits < 0)
        {
            divDate.textContent = "La date de départ est inférieure à la date d'arrivée.";
            dateFin.classList.add('invalid-input');
            return false;
        }
        else if(nbreNuits == 0)
        {
            divDate.textContent = "La date de départ est égale à la date d'arrivée.";
            dateFin.classList.add('invalid-input');
            return false;
        }
        else
        {
            divDate.textContent = "";
            dateFin.classList.remove('invalid-input');
            document.getElementById('dureeReserv').value = nbreNuits;
            return true;
        }
    }
}

/**
 * Affiche les champs pour choisir le type d'hébergement en haute saison, les supprime si la saison est basse
 * @param {string} saison Haute ou basse
 */
function afficherHauteSaison(saison)
{
    let type = document.getElementById("type-hebergement");
    
    if(saison == "haute" && type.innerText.trim() == "")
    {
        type.innerHTML = "";
        let htmlForm = '<div class="form-group mb-4">';
        htmlForm += '<div class="row">';
        htmlForm += '<legend class="col-lg-2 col-sm-4 col-form-label pt-0">Type d\'hébergement</legend>';
        htmlForm += '<div class="col-sm-10">';
        htmlForm += '<div class="custom-control custom-radio">';
        htmlForm += '<input type="radio" id="hebergement-dur" name="TYPE_HEBERGEMENT" value="dur" class="custom-control-input" required>';
        htmlForm += '<label for="hebergement-dur" class="custom-control-label">Hébergement en dur (chambre de 2 à 6 lits)</label>';
        htmlForm += '<div id="prix-personne-dur" class="prix-hebergement"><span class="prix-personne"></span><span class="label-prix-personne"></span></div>'
        htmlForm += '</div>';
        htmlForm += '<div class="custom-control custom-radio">';
        htmlForm += '<input type="radio" id="hebergement-toile" name="TYPE_HEBERGEMENT" value="toile" class="custom-control-input" required>';
        htmlForm += '<label for="hebergement-toile" class="custom-control-label">Hébergement sous toile (marabout ou bungali)</label>';
        htmlForm += '<div id="prix-personne-toile" class="prix-hebergement"><span class="prix-personne"></span><span class="label-prix-personne"></span></div>'
        htmlForm += '</div>';
        htmlForm += '</div>';
        htmlForm += '</div>';
        htmlForm += '</div>';
        type.innerHTML = htmlForm;
        // On met les listeners pour "désactiver la gestion libre" et afficher le prix/personne (fonction ready)
        for(radio of document.querySelectorAll('[name=TYPE_HEBERGEMENT]'))
        {
            radio.addEventListener('change', function(e) { gestionLibreDisabled(e, radio.value) });
            radio.addEventListener('change', ready);
        }

        document.querySelectorAll('span.prix').forEach( (span) => {
            span.innerText = "";
        })
    
        document.querySelectorAll('span.label-prix').forEach( (span) => {
            span.innerText = "";
        })    
    }
    // Si le jour des dates début + fin change mais que l'on reste en haute saison
    else if(saison == "haute")
    {
        return;
    }
    else
    {
        type.innerHTML = "";
    }
}

/**
 * Fonction de callback pour les type d'hébergements (haute saison) : désactive ou non le champs gestion libre
 * @param {DOM Element} e déclencheur de l'évènement
 * @param {DOM Element} pension type de pension
 */
function gestionLibreDisabled(e, pension)
{
    if(e.target.value == "dur")
    {
        // Pour décocher la case si elle l'était puis que l'utilisateur clique sur "Hébergement en dur"
        if(document.getElementById('gestionlibre').checked)
        {
            document.getElementById('gestionlibre').checked = false;
            afficherChapiteau(pension, document.getElementById('dureeReserv'), "haute");
        }
        document.getElementById('gestionlibre').setAttribute('disabled', '');
    }
    else if(e.target.value = "toile")
    {
        document.getElementById('gestionlibre').removeAttribute('disabled', '');
    }
}

/**
 *  Détermine si on peut calculer le prix d'hébergement
 */
function ready()
{
    let duree = parseInt(document.getElementById('dureeReserv').value, 10);
    let total = parseInt(document.getElementById('nbTotal').value, 10);
    let nbEnfants = parseInt(document.getElementById('nbEnfants').value, 10);
    let nbAdultes = parseInt(document.getElementById('nbAdultes').value, 10);
    let date = new Date(document.getElementById('dateDebutReserv').value);
    let periode = date.getMonth() + 1;
    // On parcourt tous les boutons radios pour savoir si un d'entre eux est coché
    for(radio of document.querySelectorAll('[name=TYPE_PENSION]'))
    {
        if(radio.checked && document.getElementById('dureeReserv').value != "" && document.getElementById('nbTotal').value != "")
        {
            if(document.getElementById("saison").value == "basse")
            {
                afficherHauteSaison("basse");
                afficherChapiteau(radio.value, duree, "basse");
                if(document.getElementById('typeGroupe').value == "scolaire")
                {
                    let niveau = document.getElementById('nivScolaire').value;
                    if(niveau == "primaire")
                    {
                        // Params : nbEnfants, 0, nbAdultes
                        calculPrixBasseSaison(radio.value, duree, total, niveau, nbEnfants, 0, nbAdultes);
                    }
                    else if(niveau == "collège")
                    {
                        // Params : 0, nbEnfants (= nbAdos), nbAdultes
                        calculPrixBasseSaison(radio.value, duree, total, niveau, 0, nbEnfants, nbAdultes);
                    }
                    else if(niveau == "lycée")
                    {
                        // Params : 0, 0, nbEnfants + nbAdultes (= nbAdultes)
                        calculPrixBasseSaison(radio.value, duree, total, niveau, 0, 0, nbAdultes + nbEnfants);
                    }
                }
                else
                {
                    calculPrixBasseSaison(radio.value, duree, total, null, nbEnfants, parseInt(document.getElementById('nbAdos').value, 10), nbAdultes);
                }
            }
        }
        if(document.getElementById("saison").value == "haute" && document.getElementById('dureeReserv').value != "" && document.getElementById('nbTotal').value != "")
        {
            for(radio2 of document.querySelectorAll('[name=TYPE_HEBERGEMENT]'))
            {
                if(radio2.checked && radio2.value == "dur")
                {
                    let typeHebergement = "dur";
                    let prixPersonne = affichePrixPersonne(typeHebergement, periode, duree);
                    if(radio.checked)
                    {
                        calculPrixHauteSaison(typeHebergement, prixPersonne, radio.value, duree, total, nbEnfants, parseInt(document.getElementById('nbAdos').value, 10), nbAdultes);
                    }
                }
                if(radio2.checked && radio2.value == "toile")
                {
                    let typeHebergement = "toile";
                    let prixPersonne = affichePrixPersonne(typeHebergement, periode, duree);
                    if(radio.checked)
                    {
                    calculPrixHauteSaison(typeHebergement, prixPersonne, radio.value, duree, total, nbEnfants, parseInt(document.getElementById('nbAdos').value, 10), nbAdultes);
                    }
                }
            }            
        }
    }
}

/**
 * Calcule le prix pour la basse saison
 * @param {string} pension type de pension : pensioncomplète, demipension ou gestionlibre
 * @param {int} duree durée du séjour = nombre de nuits
 * @param {int} nbrePersonnes taille du groupe
 * @param {string} niveau primaire, collège ou lycée
 * @param {int} nbEnfants nombre d'enfants
 * @param {int} nbAdos nombre d'adolescents
 * @param {int} nbAdultes nombre d'adultes
 */
function calculPrixBasseSaison(pension, duree, nbrePersonnes, niveau, nbEnfants, nbAdos, nbAdultes)
{
    let prixGL = 0;
    let prixPCEnfants = 0;
    let prixPCAdos = 0;
    let prixPCAdultes = 0;
    let prixDPEnfants = 0;
    let prixDPAdos = 0;
    let prixDPAdultes = 0;

    // On récupère les span prix et label-prix
    let prixUniteGL = document.querySelector('#prix-gestionlibre > span.prix');
    let labelGL = document.querySelector('#prix-gestionlibre > span.label-prix');

    labelGL.innerText = "";

    let prixUnitePCEnfants = document.querySelector('#prix-pensioncomplète-enfants > span.prix');
    let prixUnitePCAdos = document.querySelector('#prix-pensioncomplète-ados > span.prix');
    let prixUnitePCAdultes = document.querySelector('#prix-pensioncomplète-adultes > span.prix');
    let labelPCEnfants = document.querySelector('#prix-pensioncomplète-enfants > span.label-prix');
    let labelPCAdos = document.querySelector('#prix-pensioncomplète-ados > span.label-prix');
    let labelPCAdultes = document.querySelector('#prix-pensioncomplète-adultes > span.label-prix');

    prixUnitePCEnfants.innerText = "";
    prixUnitePCAdos.innerText = "";
    prixUnitePCAdultes.innerText = "";
    labelPCEnfants.innerText = "";
    labelPCAdos.innerText = "";
    labelPCAdultes.innerText = "";

    let prixUniteDPEnfants = document.querySelector('#prix-demipension-enfants > span.prix');
    let prixUniteDPAdos = document.querySelector('#prix-demipension-ados > span.prix');
    let prixUniteDPAdultes = document.querySelector('#prix-demipension-adultes > span.prix');
    let labelDPEnfants = document.querySelector('#prix-demipension-enfants > span.label-prix');
    let labelDPAdos = document.querySelector('#prix-demipension-ados > span.label-prix');
    let labelDPAdultes = document.querySelector('#prix-demipension-adultes > span.label-prix');

    prixUniteDPEnfants.innerText = "";
    prixUniteDPAdos.innerText = "";
    prixUniteDPAdultes.innerText = "";
    labelDPEnfants.innerText = "";
    labelDPAdos.innerText = "";
    labelDPAdultes.innerText = "";

    document.querySelectorAll('span.frais').forEach( (span) => {
        span.innerText = "";
    })

    document.querySelectorAll('span.label-frais').forEach( (span) => {
        span.innerText = "";
    })

    // GESTION LIBRE
    if(duree == 1)
    {
        if(nbrePersonnes < 30)
        {
            prixGL = prixSejours[13].prix;
        }
        else if(nbrePersonnes < 50)
        {
            prixGL = prixSejours[15].prix;
        }
        else if(nbrePersonnes < 80)
        {
            prixGL = prixSejours[17].prix;
        }
        else if(nbrePersonnes < 100)
        {
            prixGL = prixSejours[19].prix;
        }
        else if(nbrePersonnes < 150)
        {
            prixGL = prixSejours[21].prix;
        }
        
        // On applique une réduction de 20% si la date de début est en septembre ou octobre
        let date = new Date(dateDebut.value);
        let mois = date.getMonth() + 1;
        if(mois == 9 || mois == 10)
        {
            prixGL -= prixGL*(20/100);
        }

        prixUniteGL.innerText = `${prixGL}€`;
        labelGL.innerText = '/groupe/nuit';
    }
    else if(duree >= 2)
    {
        if(nbrePersonnes < 30)
        {
            prixGL = prixSejours[14].prix;
        }
        else if(nbrePersonnes < 50)
        {
            prixGL = prixSejours[16].prix;
        }
        else if(nbrePersonnes < 80)
        {
            prixGL = prixSejours[18].prix;
        }
        else if(nbrePersonnes < 100)
        {
            prixGL = prixSejours[20].prix;
        }
        else if(nbrePersonnes < 150)
        {
            prixGL = prixSejours[22].prix;
        }

        // On applique une réduction de 20% si la date de début est en septembre ou octobre
        let date = new Date(dateDebut.value);
        let mois = date.getMonth() + 1;
        if(mois == 9 || mois == 10)
        {
            prixGL -= prixGL*(20/100);
        }

        prixUniteGL.innerText = `${prixGL}€`;
        labelGL.innerText = '/groupe/nuit';
    }
    // PRIMAIRES ou ENFANTS
    if(niveau == "primaire" || nbEnfants != 0)
    {
        if(duree <= 3)
        {
            prixPCEnfants = prixSejours[1].prix;
            prixDPEnfants = prixSejours[2].prix;
        }
        else if(duree > 3)
        {
            prixPCEnfants = prixSejours[3].prix;
            prixDPEnfants = prixSejours[4].prix;
        }
        if(niveau == "primaire")
        {
            prixUnitePCAdos.innerText = "";
            prixUniteDPAdos.innerText = "";
            labelPCAdos.innerText = "";
            labelDPAdos.innerText = "";
        }
        prixUnitePCEnfants.innerText = `${prixPCEnfants}€`;
        prixUniteDPEnfants.innerText = `${prixDPEnfants}€`;
        labelPCEnfants.innerText = "/enfant/nuit";
        labelDPEnfants.innerText = "/enfant/nuit";
    }
    // COLLEGE ou ADOLESCENTS
    if(niveau == "collège" || nbAdos != 0)
    {
        if(duree <= 3)
        {
            prixPCAdos = prixSejours[5].prix;
            prixDPAdos = prixSejours[6].prix;
        }
        else if(duree > 3)
        {
            prixPCAdos = prixSejours[7].prix;
            prixDPAdos = prixSejours[8].prix;
        }
        prixUnitePCAdos.innerText = `${prixPCAdos}€`;
        prixUniteDPAdos.innerText = `${prixDPAdos}€`;
        labelPCAdos.innerText = "/ado/nuit";
        labelDPAdos.innerText = "/ado/nuit";
        if(niveau == "collège")
        {
            prixUnitePCEnfants.innerText = "";
            prixUniteDPEnfants.innerText = "";
            labelPCEnfants.innerText = "";
            labelDPEnfants.innerText = "";
        }
    }
    // LYCEE ou ADULTES
    if(niveau == "lycée" || nbAdultes != 0)
    {
        if(duree <= 3)
        {
            prixPCAdultes = prixSejours[9].prix;
            prixDPAdultes = prixSejours[10].prix;
        }
        else if(duree > 3)
        {
            prixPCAdultes = prixSejours[11].prix;
            prixDPAdultes = prixSejours[12].prix;
        }
        prixUnitePCAdultes.innerText = `${prixPCAdultes}€`;
        prixUniteDPAdultes.innerText = `${prixDPAdultes}€`;
        labelPCAdultes.innerText = "/adulte/nuit";
        labelDPAdultes.innerText = "/adulte/nuit";
        if(niveau == "lycée")
        {
            prixUnitePCEnfants.innerText = "";
            prixUniteDPEnfants.innerText = "";
            labelPCEnfants.innerText = "";
            labelDPEnfants.innerText = "";
            prixUnitePCAdos.innerText = "";
            prixUniteDPAdos.innerText = "";
            labelPCAdos.innerText = "";
            labelDPAdos.innerText = "";
        }
    }
    if(pension == "pensioncomplète")
    {
        prixHebergement = duree * prixPCEnfants * nbEnfants + duree * prixPCAdos * nbAdos + duree * prixPCAdultes * nbAdultes;
        calculPrixTotal(prixHebergement, prixFraisActivites, prixFraisOptionnels);
    }
    else if(pension == "demipension")
    {
        prixHebergement = duree * prixDPEnfants * nbEnfants + duree * prixDPAdos * nbAdos + duree * prixDPAdultes * nbAdultes;
        calculPrixTotal(prixHebergement, prixFraisActivites, prixFraisOptionnels);
    }
    else if(pension == "gestionlibre")
    {
        prixHebergement = duree * prixGL;
        calculPrixTotal(prixHebergement, prixFraisActivites, prixFraisOptionnels);
    }
    divPrixHebergement.innerText = prixHebergement;
}

/**
 * Affiche le prix par personne pour l'hébergement en dur ou en toile, retourne le prix/personne
 * @param {string} typeHebergement dur ou toile
 * @param {int} periode juillet ou août (7 ou 8)
 * @param {int} duree nombre de nuits
 * @returns {int} prixPersonne prix/personne
 */
function affichePrixPersonne(typeHebergement, periode, duree)
{
    let prixPersonne = 0;

    let prixUnitePersonneDur = document.querySelector('#prix-personne-dur > span.prix-personne');
    let labelPersonneDur = document.querySelector('#prix-personne-dur > span.label-prix-personne');
    prixUnitePersonneDur.innerText = "";
    labelPersonneDur.innerText = "";

    let prixUnitePersonneToile = document.querySelector('#prix-personne-toile > span.prix-personne');
    let labelPersonneToile = document.querySelector('#prix-personne-toile > span.label-prix-personne');
    prixUnitePersonneToile.innerText = "";
    labelPersonneToile.innerText = "";

    let labelGL = document.querySelector('#frais-gestionlibre > span.label-frais');
    labelGL.innerText = "";


    if(typeHebergement == "dur")
    {
        if(periode == 7)
        {
            if(duree <= 5)
            {
                prixPersonne = prixSejours[29].prix;
            }
            else if(duree > 5)
            {
                prixPersonne = prixSejours[30].prix;
            }
        }
        else if(periode == 8)
        {
            if(duree <= 5)
            {
                prixPersonne = prixSejours[31].prix;
            }
            else if(duree > 5)
            {
                prixPersonne = prixSejours[32].prix;
            }
        }
        prixUnitePersonneDur.innerText = `${prixPersonne}€`;
        labelPersonneDur.innerText = '/personne/nuit';
        return prixPersonne;
    }
    if(typeHebergement == "toile")
    {
        prixPersonne = prixSejours[33].prix;
        prixUnitePersonneToile.innerText = `≈${prixPersonne}€`;
        labelPersonneToile.innerText = '/personne/nuit';
        labelGL.innerText = "Pas de frais de pension";
        return prixPersonne;
    }
}

/**
 * Calcule le prix pour la haute saison
 * @param {string} typeHebergement dur ou toile
 * @param {int} prixPersonne prix/personne
 * @param {string} pension type de pension : pensioncomplète, demipension ou gestionlibre
 * @param {int} duree durée du séjour = nombre de nuits
 * @param {int} nbrePersonnes taille du groupe
 * @param {string} niveau primaire, collège ou lycée
 * @param {int} nbEnfants nombre d'enfants
 * @param {int} nbAdos nombre d'adolescents
 * @param {int} nbAdultes nombre d'adultes
 */
function calculPrixHauteSaison(typeHebergement, prixPersonne, pension, duree, nbrePersonnes, nbEnfants, nbAdos, nbAdultes)
{
    let fraisPCEnfants = 0;
    let fraisPCAdos = 0;
    let fraisPCAdultes = 0;
    let fraisDPEnfants = 0;
    let fraisDPAdos = 0;
    let fraisDPAdultes = 0;
    let prixChapiteau = 0;
    // Total du prix d'hébergement
    let prixPersonneTotal = 0;
    // Total frais de pension
    let prixPensionTotal = 0;

    // On récupère les span prix et label-prix pour le prix/personne et les frais
    let fraisUnitePCEnfants = document.querySelector('#frais-pensioncomplète-enfants > span.frais');
    let fraisUnitePCAdos = document.querySelector('#frais-pensioncomplète-ados > span.frais');
    let fraisUnitePCAdultes = document.querySelector('#frais-pensioncomplète-adultes > span.frais');
    let labelPCEnfants = document.querySelector('#frais-pensioncomplète-enfants > span.label-frais');
    let labelPCAdos = document.querySelector('#frais-pensioncomplète-ados > span.label-frais');
    let labelPCAdultes = document.querySelector('#frais-pensioncomplète-adultes > span.label-frais');

    fraisUnitePCEnfants.innerText = "";
    fraisUnitePCAdos.innerText = "";
    fraisUnitePCAdultes.innerText = "";
    labelPCEnfants.innerText = "";
    labelPCAdos.innerText = "";
    labelPCAdultes.innerText = "";

    let fraisUniteDPEnfants = document.querySelector('#frais-demipension-enfants > span.frais');
    let fraisUniteDPAdos = document.querySelector('#frais-demipension-ados > span.frais');
    let fraisUniteDPAdultes = document.querySelector('#frais-demipension-adultes > span.frais');
    let labelDPEnfants = document.querySelector('#frais-demipension-enfants > span.label-frais');
    let labelDPAdos = document.querySelector('#frais-demipension-ados > span.label-frais');
    let labelDPAdultes = document.querySelector('#frais-demipension-adultes > span.label-frais');

    fraisUniteDPEnfants.innerText = "";
    fraisUniteDPAdos.innerText = "";
    fraisUniteDPAdultes.innerText = "";
    labelDPEnfants.innerText = "";
    labelDPAdos.innerText = "";
    labelDPAdultes.innerText = "";

    document.querySelectorAll('span.prix').forEach( (span) => {
        span.innerText = "";
    })

    document.querySelectorAll('span.label-prix').forEach( (span) => {
        span.innerText = "";
    })

    // FRAIS DE PENSION
    if(nbEnfants != 0)
    {
        // Pension complète
        fraisPCEnfants = prixSejours[23].prix;
        fraisUnitePCEnfants.innerText = `${fraisPCEnfants}€`;
        labelPCEnfants.innerText = '/enfant/jour';
        // Demi-pension
        fraisDPEnfants = prixSejours[26].prix;
        fraisUniteDPEnfants.innerText = `${fraisDPEnfants}€`;
        labelDPEnfants.innerText = '/enfant/jour';
    }
    if(nbAdos != 0)
    {
        // Pension complète
        fraisPCAdos = prixSejours[24].prix;
        fraisUnitePCAdos.innerText = `${fraisPCAdos}€`;
        labelPCAdos.innerText = '/ado/jour';
        // Demi-pension
        fraisDPAdos = prixSejours[27].prix;
        fraisUniteDPAdos.innerText = `${fraisDPAdos}€`;
        labelDPAdos.innerText = '/ado/jour';

    }
    if(nbAdultes != 0)
    {
        // Pension complète
        fraisPCAdultes = prixSejours[25].prix;
        fraisUnitePCAdultes.innerText = `${fraisPCAdultes}€`;
        labelPCAdultes.innerText = '/adulte/jour';
        // Demi-pension
        fraisDPAdultes = prixSejours[28].prix;
        fraisUniteDPAdultes.innerText = `${fraisDPAdultes}€`;
        labelDPAdultes.innerText = '/adulte/jour';
    }
    if(pension == "pensioncomplète")
    {
        afficherChapiteau(pension, duree), "haute";
        prixPensionTotal = fraisPCEnfants * nbEnfants * duree + fraisPCAdos * nbAdos * duree + fraisPCAdultes * nbAdultes * duree;
    }
    if(pension == "demipension")
    {
        afficherChapiteau(pension, duree, "haute");
        prixPensionTotal = fraisDPEnfants * nbEnfants * duree + fraisDPAdos * nbAdos * duree + fraisDPAdultes * nbAdultes * duree;
    }
    if(typeHebergement == "dur")
    {
        afficherChapiteau(pension, duree, "haute");
    }
    if(typeHebergement == "toile")
    {
        if(pension == "gestionlibre")
        {
            prixPersonne = prixSejours[33].prix;
            prixPensionTotal = 0;
            afficherChapiteau(pension, duree, "haute");
        }
    }
    prixPersonneTotal = prixPersonne * nbrePersonnes * duree;
    prixHebergement = prixPersonneTotal + prixPensionTotal + prixChapiteau;
    divPrixHebergement.innerText = prixHebergement;
    calculPrixTotal(prixHebergement, prixFraisActivites, prixFraisOptionnels);
}

/**
 * Affiche le champs chapiteau cuisine si la gestion libre est sélectionnée
 * @param {string} pension pensioncomplète, demipension ou gestionlibre
 * @param {string} duree nombre de nuits
 * @param {string} saison basse ou haute 
 */
function afficherChapiteau(pension, duree, saison)
{
    let div = document.getElementById("chapiteau-cuisine");
    
    if(pension == "gestionlibre" && div.innerHTML.trim() == "" && saison == "haute")
    {
        div.innerHTML = "";
        let htmlForm = '<div class="custom-control custom-checkbox">';
        htmlForm += '<input type="checkbox" id="chapiteau" name="CHAPITEAU" value="chapiteau" class="custom-control-input">';
        htmlForm += '<label for="chapiteau" class="custom-control-label">Chapiteau cuisine ou salle à manger</label>';
        htmlForm += '</div>';
        div.innerHTML = htmlForm;
        document.getElementById('chapiteau').addEventListener('change', function() { prixChapiteau(duree); });
    }
    else
    {
        div.innerHTML = "";
    }
}

/**
 * Fonction de callback du listener sur la case à cocher Chapiteau cuisine, retourne le prix du chapiteau
 * @param {int} duree nombre de nuits
 */
function prixChapiteau(duree)
{
    let prixChapiteau = 0;
    if(duree <= 5)
    {
        prixChapiteau = prixSejours[34].prix;
    }
    else if(duree > 5)
    {
        prixChapiteau = prixSejours[35].prix;
    }
    if(document.getElementById('chapiteau').checked)
    {
        prixHebergement += prixChapiteau;
        divPrixHebergement.innerText = prixHebergement;
        calculPrixTotal(prixHebergement, prixFraisActivites, prixFraisOptionnels);
    }
    if(!document.getElementById('chapiteau').checked)
    {
        prixHebergement -= prixChapiteau;
        divPrixHebergement.textContent = prixHebergement;
        calculPrixTotal(prixHebergement, prixFraisActivites, prixFraisOptionnels);
    }
}

/* ---------------------------------------------------------------- CALCUL PRIX ACTIVITES -----------------------------------------------------------------*/

let activiteListe = document.querySelectorAll("[name='ACTIVITES[]']");

activiteListe.forEach( (activite) => {

    // LET
    let seancesField = document.getElementById("seances" + activite.value);
    let partsField = document.getElementById("participants" + activite.value);
    let personnesParGroupe = document.getElementById("personnes-" + activite.value);
    let prixSpan = document.getElementById("prixUnite" + activite.value);
    let estimation = document.getElementById("estimation-" + activite.value);
    let champPrixActivite = document.getElementById("prixActivite-" + activite.value);
    let div = document.getElementById("div-" + activite.value);
    let checkbox = document.getElementById("input" + activite.value);
    let euro = document.getElementById("euro-" + activite.value);

    // LISTENERS
    seancesField.addEventListener("input", function() { getSeancesParticipantsPrix(estimation, champPrixActivite, seancesField, partsField, personnesParGroupe, prixSpan, euro); });
    partsField.addEventListener("input", function() { getSeancesParticipantsPrix(estimation, champPrixActivite, seancesField, partsField, personnesParGroupe, prixSpan, euro); });
    div.addEventListener("click", function(e) { check(e, checkbox, estimation, champPrixActivite, seancesField, partsField, personnesParGroupe, prixSpan, euro); });
});

/**
 * Fonction callback de l'écouteur d'évènement sur la div activite. Check la checkbox si elle n'est pas cochée et la décoche dans le cas inverse. Si les champs seances ou participants sont cliqués, il ne se passe rien.
 * @param {DOM Element} e élément déclencheur de l'évènement
 * @param {DOM Element} checkbox checkbox contenue dans la div parent
 * @param {DOM Element} estimation span contenant le prix total de l'activité
 * @param {DOM Element} partsField champ nombre participants
 * @param {DOM Element} seancesField champ nombre séances
 * @param {DOM Element} personnesParGroupe span, contient le nombre de personnes par groupe de l'activité
 * @param {DOM Element} prixSpan span, affiche le prix total pour l'activité
 * @param {DOM Element} euro span, contient le signe "€"
 */
function check(e, checkbox, estimation, champPrixActivite, seancesField, partsField, personnesParGroupe, prixSpan, euro)
{
    if(e.target == partsField || e.target == seancesField)
    {
        // On ne touche pas à la checkbox, on laisse l'utilisateur entrer des valeurs
        return;
    }
    // On change (= met à jour) d'abord l'état de la checkbox avant de calculer le prix, sinon on lui envoie l'état passé
    if(!checkbox.checked)
    {
        checkbox.checked = true;
        seancesField.removeAttribute("disabled");
        partsField.removeAttribute("disabled");
        champPrixActivite.removeAttribute("disabled");
        seancesField.setAttribute("required", "");
        partsField.setAttribute("required", "");
    }
    else
    {
        checkbox.checked = false;
        estimation.innerText = "";
        euro.classList.add('invisible');
        seancesField.setAttribute("disabled", "");
        partsField.setAttribute("disabled", "");
        champPrixActivite.setAttribute("disabled", "");
        seancesField.removeAttribute("required");
        partsField.removeAttribute("required");
        // Calcul pour enlever le prix de l'activité qui vient d'être déselectionnée
        calculPrixActivites(document.getElementById("prixOption-3").value);
        // Pour éviter de faire le if qui suit, activité déselectionnée donc pas de calcul de son prix
        return;
    }
    // Si les champs ne sont pas vides quand on clique sur la div, on calcule et affiche le prix
    if(partsField.value != "" || seancesField.value != "")
    {
        getSeancesParticipantsPrix(estimation, champPrixActivite, seancesField, partsField, personnesParGroupe, prixSpan, euro);
    }
}

/**
 * Fonction callback de l'écouteur d'évènement sur les champs participants et séances. Calcule le prix de l'activité
 * @param {DOM Element} estimation champs texte, affiche le prix de l'activité à l'unité
 * @param {DOM Element} seancesField champs input du nombre de séances
 * @param {DOM Element} partsField champs input du nombre de participants
 * @param {DOM Element} personnesParGroupe span, contient le nombre de personnes par groupe de l'activité
 * @param {DOM Element} prixSpan span, affiche le prix total pour l'activité
 * @param {DOM Element} euro span, contient le signe "€"
 */
function getSeancesParticipantsPrix(estimation, champPrixActivite, seancesField, partsField, personnesParGroupe, prixSpan, euro)
{
    let seance = parseInt(seancesField.value, 10);
    let parts = parseInt(partsField.value, 10);
    let personnes = parseInt(personnesParGroupe.textContent, 10);
    let prix = parseFloat(prixSpan.textContent, 10);
    let prixFinal = undefined;
    // Nombre de groupes de x(prs) personnes arrondi au supérieur * nombre de séances par groupe * prix d'une séance
    if(Number.isInteger(parts) && Number.isInteger(seance))
    {
        prixFinal = Math.ceil(parts/personnes)*seance*prix;
    }
    if(!isNaN(prixFinal))
    {
        prixActivite = prixFinal;
        estimation.innerText = prixActivite;
        champPrixActivite.value = prixActivite;
        euro.classList.remove('invisible');
        // Calcul pour ajouter le prix de l'activité sélectionnée
        calculPrixActivites(document.getElementById("prixOption-3").value);
    }
}

/**
 * Calcule le prix total des activités
 * @param {int} prixProgrammationActivites
 */
function calculPrixActivites(prixProgrammationActivites)
{
    if(prixProgrammationActivites == "")
    {
        prixProgrammationActivites = 0;
    }
    else
    {
        prixProgrammationActivites = parseInt(prixProgrammationActivites, 10);
    }
    prixActivites = 0;
    let activiteListe = document.querySelectorAll("[name='ACTIVITES[]']");
    activiteListe.forEach( (activite) => {
        // Si l'activité est cochée
        if(document.getElementById("input" + activite.value).checked)
        {
            // Et que le prix n'est pas vide
            if(document.getElementById("estimation-" + activite.value).innerText != "")
            {
                // Alors on l'ajoute au prix total
                prixActivites += parseFloat(document.getElementById("estimation-" + activite.value).innerText, 10);
            }
        }
    });
    prixFraisActivites = prixActivites + prixProgrammationActivites;
    prixFraisActivites = Math.round(prixFraisActivites*100)/100;
    divPrixActivites.innerText = prixFraisActivites;
    calculPrixTotal(prixHebergement, prixFraisActivites, prixFraisOptionnels);
}



/* ------------------------------------------------------------- CALCUL PRIX FRAIS OPTIONNELS --------------------------------------------------------------*/

// Listeners
let options = document.querySelectorAll("[name='OPTIONS[]']");
options.forEach( (option) => {
    option.addEventListener('change', take);
} )


/**
 * Calcule et affiche le prix pour l'option sélectionnée
 * @param {event} e élément déclencheur de l'évènement : bouton radio des frais optionnels
 */
function take(e)
{
    let prixOptionPersonne = 0;
    let prixOptionTotal = 0;
    let prix = document.querySelector(`#prix-${e.target.id} > span.prix`);
    let label = document.querySelector(`#prix-${e.target.id} > span.label-prix`);
    let champPrixOption = document.getElementById("prixOption-" + e.target.value);
    let nbreParticipants = parseInt(document.getElementById('nbEnfants').value, 10);
    if(document.getElementById('nbAdos') != null)
    {
        nbreParticipants += parseInt(document.getElementById('nbAdos').value, 10);
    }
    // Assurance annulation individuelle : 2€/jour/participant (= enfants, ados et lycéens)
    if(e.target.id == "option1")
    {
        if(document.getElementById('dureeReserv').value > 8.5)
        {
            prixOptionPersonne = 15;
            prixOptionTotal = 15 * nbreParticipants;
        }
        else
        {
            prixOptionPersonne = prixOptions[1].prix * parseInt(document.getElementById('dureeReserv').value, 10);
            prixOptionTotal = prixOptionPersonne * nbreParticipants;
        }
        if(prix.innerText == "" && label.innerText == "")
        {
            prix.innerText = `${prixOptionPersonne}€`;
            label.innerText = "/participant";
            champPrixOption.removeAttribute("disabled");
            champPrixOption.value = prixOptionTotal;
            prixFraisOptionnels += prixOptionTotal;
        }
        else
        {
            prix.innerText = "";
            label.innerText = "";
            champPrixOption.setAttribute("disabled", "");
            prixFraisOptionnels -= prixOptionTotal;
        }
    }
    // Assurance annulation groupe : prixTotal (sans frais optionnel = prixHebergement + prixFraisActivites) * 6%
    else if(e.target.id == "option2")
    {
        prixOptionTotal = Math.round((parseInt(prixHebergement, 10) + parseFloat(prixFraisActivites, 10)) * (6 / 100) * 100)/100;
        if(prix.innerText == "" && label.innerText == "")
        {
            prix.innerText = `${prixOptionTotal}€`;
            champPrixOption.removeAttribute("disabled");
            champPrixOption.value = prixOptionTotal;
            prixFraisOptionnels += prixOptionTotal;
        }
        else
        {
            prix.innerText = "";
            champPrixOption.setAttribute("disabled", "");
            prixFraisOptionnels -= prixOptionTotal;
        }
    }
    // Programmation des activités : 2€/jour/participant
    else if(e.target.id == "option3")
    {
        prixOptionPersonne = prixOptions[3].prix * parseInt(document.getElementById('dureeReserv').value, 10);
        prixOptionTotal = prixOptionPersonne * nbreParticipants;
        if(prix.innerText == "" && label.innerText == "")
        {
            prix.innerText = `${prixOptionPersonne}€`;
            label.innerText = "/participant";
            champPrixOption.removeAttribute("disabled");
            champPrixOption.value = prixOptionTotal;
            calculPrixActivites(prixOptionTotal);
        }
        else
        {
            prix.innerText = "";
            label.innerText = "";
            champPrixOption.setAttribute("disabled", "");
            calculPrixActivites(0);
        }   
    }
    // Mise à disposition de draps : 5€ par paire
    else if(e.target.id == "option4")
    {
        prixOptionTotal = prixOptions[4].prix * parseInt(document.getElementById('nbTotal').value, 10);
        if(prix.innerText == "" && label.innerText == "")
        {
            prix.innerText = `${prixOptions[4].prix}€`;
            label.innerText = "/personne";
            champPrixOption.removeAttribute("disabled");
            champPrixOption.value = prixOptionTotal;
            prixFraisOptionnels += prixOptionTotal;
        }
        else
        {
            prix.innerText = "";
            label.innerText = "";
            champPrixOption.setAttribute("disabled", "");
            prixFraisOptionnels -= prixOptionTotal;
        }
    }
    // Pour toutes les autres options rajoutées par le gérant
    else
    {
        // champPrixOption.id.substr(11) = id de l'option (prixOption-id)
        console.log(champPrixOption.id.substr(11));
        prixOptionTotal = prixOptions[champPrixOption.id.substr(11)].prix;
        if(prix.innerText == "" && label.innerText == "")
        {
            prix.innerText = `${prixOptions[champPrixOption.id.substr(11)].prix}€`;
            champPrixOption.removeAttribute("disabled");
            champPrixOption.value = prixOptionTotal;
            prixFraisOptionnels += prixOptionTotal;
        }
        else
        {
            prix.innerText = "";
            champPrixOption.setAttribute("disabled", "");
            prixFraisOptionnels -= prixOptionTotal;
        }
    }
    // // Mise à disposition de trottinettes
    // if(e.target.id == "option5")
    // {
    //     prixOptionTotal = prixOptions[5].prix (= 0);
    //     if(prix.innerText == "")
    //     {
    //         prix.innerText = "Gratuit";
    //         champPrixOption.removeAttribute("disabled");
    //         champPrixOption.value = prixOptionTotal;
    //     }
    //     else
    //     {
    //         prix.innerText = "";
    //         champPrixOption.setAttribute("disabled", "");
    //     }       
    // }
    prixFraisOptionnels = Math.round(prixFraisOptionnels*100)/100;
    divPrixFraisOptionnels.innerText = prixFraisOptionnels;
    calculPrixTotal(prixHebergement, prixFraisActivites, prixFraisOptionnels);
}



/* ----------------------------------------------------------------- CALCUL PRIX TOTAL ----------------------------------------------------------------------*/


/**
 * Calcule le prix total du devis et l'affiche
 * @param {int} prixHebergement prix total pour l'hébergement
 * @param {int} prixFraisActivites prix total pour les activités
 * @param {int} prixFraisOptionnels prix total pour les frais otpionnels
 */
function calculPrixTotal(prixHebergement, prixFraisActivites, prixFraisOptionnels)
{
    prixTotal = Math.round((prixHebergement + prixFraisActivites + prixFraisOptionnels)*100)/100;
    champPrixHebergement.setAttribute('value', prixHebergement);
    champPrixActivites.setAttribute('value', prixFraisActivites);
    champPrixFraisOptionnels.setAttribute('value', prixFraisOptionnels);
    champPrixTotal.setAttribute('value', prixTotal);
    divPrixTotal.innerText = prixTotal;
}

/* ---------------------------------------------- VÉRIFICATION PROGRAMMATION ACTIVITÉS AVANT VALIDATION DU FORMULAIRE ---------------------------------------*/

// Marche aussi avec addEventListener('click'), preventDefault au début du script puis document.getElementById('form').submit()
document.getElementById('form').addEventListener('submit', verifyActivites);
function verifyActivites(e)
{
    if(document.getElementById('option3').checked)
    {
        let i = 0;
        for(activite of document.querySelectorAll("[name='ACTIVITES[]']"))
        {
            if(activite.checked)
            {
                i++;
            }
        }
        if(i == 0)
        {
            e.preventDefault();
            if(document.getElementById("block-confirmation").textContent == "")
            {
                let html = "<div id='confirmation' class='mb-4'>";
                html += "<p class='mb-0'>Aucune activité n'est sélectionnée alors que vous avez choisi la programmation activités.<br> Veuillez sélectionner au moins une activité ou renoncez à la programmation activités.</p>";
                html += "</div>";
                document.getElementById("block-confirmation").innerHTML = html;
            }    
        }
        else
        {
            return;
        }
    }
}