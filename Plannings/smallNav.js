document.getElementById('eye').addEventListener('click', cacher);
function cacher(e)
{
    document.getElementById('navbar').classList.toggle('hide');
    e.target.classList.toggle("openBlue");
    e.target.classList.toggle("closedBlue");
    // Cookie icône oeil
    let cookie1 = getCookie("eye");
    if(cookie1 == "closedBlue")
    {
        document.cookie = "eye=openBlue";
    }
    else
    {
        document.cookie = "eye=closedBlue";
    }
    // Cookie affichage navBar
    let cookie2 = getCookie("hide");
    if(cookie2 == "")
    {
        document.cookie = "hide=hide";
    }
    else
    {
        document.cookie = "hide=";
    }
}

document.getElementById('compression').addEventListener('click', compression);
function compression(e)
{
    e.target.classList.toggle("compression");
    e.target.classList.toggle("large");

    let cookie3 = getCookie("compression");
    if(cookie3 == "compression")
    {
        document.cookie = "compression=large";
    }
    else
    {
        document.cookie = "compression=compression";
    }

    // Les toggle sont utiles pour changer le DOM affiché. Le classe déterminée par le cookie sert au chargement de la page
    document.querySelector("thead").classList.toggle("compress");
    document.querySelector("h1").classList.toggle("compress");
    document.getElementById("currentMonth").classList.toggle("compress");
    let weekdays = document.querySelectorAll(".calendar__weekday");
    let calendarDays = document.querySelectorAll(".calendar__day");
    let days = document.querySelectorAll(".day");
    let othersMonth = document.querySelectorAll(".calendar__othermonth");
    let allDevis = document.querySelectorAll(".devis");
    let notConfirmed = document.querySelectorAll(".not-confirmed");
    toggle(weekdays);
    toggle(calendarDays);
    toggle(days);
    toggle(othersMonth);
    toggle(allDevis);
    toggle(notConfirmed);

    let smallNav = document.getElementById("smallNav");
    smallNav.classList.toggle("compress");

    // On chnage la valeur du cookie après avoir fait tous les toggle
    let cookie4 = getCookie("compress");
    if(cookie4 == "compress")
    {
        document.cookie = "compress=";
    }
    else
    {
        document.cookie = "compress=compress";
    }
}

// https://www.analyste-programmeur.com/javascript/cookies/lire-un-cookie-javascript
function getCookie(name)
{
    var regSepCookie = new RegExp('(; )', 'g');
    var cookies = document.cookie.split(regSepCookie);

    for(var i = 0; i < cookies.length; i++)
    {
        var regInfo = new RegExp('=', 'g');
        var infos = cookies[i].split(regInfo);
        if(infos[0] == name)
        {
            return unescape(infos[1]);
        }
    }
}

function toggle(listeElements)
{
    for(element of listeElements)
    {
        element.classList.toggle("compress");
    }
}