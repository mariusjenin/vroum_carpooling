let star_rating = $('.star_rating');
star_rating.click(change_star_rating);
star_rating.mouseenter(change_star_glow);
$('.star_rating[data-num_star=\'1\']').each(function (){
        $($(this)[0].parentNode).mouseleave(remove_star_glow);
});

/**
 * Selectionne la star selectionnée et ses soeurs précédents
 */
function change_star_rating(){
    let star_cour=$(this);
    let id_user = $(this)[0].parentNode.dataset.userid;
    let trip_id = $(this)[0].parentNode.dataset.trip_id;
    let rate_url = $(this)[0].parentNode.dataset.rate_url;
    let num_star = $(this)[0].dataset.num_star;

    //Requete ajax pour changer la note du covoitureur
    $.ajax(
        {
            url: rate_url,
            type: 'POST',
            data: {
                trip_id: trip_id,
                id_user: id_user,
                num_star: num_star
            }
        }
    ).done(function (res) {
        star_cour.addClass("bi-star-fill");
        star_cour.removeClass("bi-star");
        star_cour.removeClass("disabled_star");
        star_cour.siblings().each(function() {
            if($(this)[0].dataset.num_star > num_star){
                $(this).addClass("bi-star");
                $(this).removeClass("bi-star-fill");
            } else {
                $(this).addClass("bi-star-fill");
                $(this).removeClass("bi-star");
            }
            $(this).removeClass("disabled_star");
        });
    });
}


/**
 * Ajoute un glow à la star selectionnée et à ses soeurs précédents
 */
function change_star_glow(){
    let num_star = $(this)[0].dataset.num_star;
    $(this).addClass("star_glow");
    $(this).siblings().each(function() {
        if($(this)[0].dataset.num_star > num_star){
            $(this).removeClass("star_glow");
        } else {
            $(this).addClass("star_glow");
        }
    });
}

/**
 * Enleve le glow de tous ses enfants (des .star_rating)
 */
function remove_star_glow(){
    $(this).removeClass("star_glow");
    $(this).children().each(function() {
        $(this).removeClass("star_glow");
    });
}

//Le bouton annuler le trajet trigger maintenant une fenêtre modale pour confirmer
modal_post_button($(".modal_cancel_trip_consult_trip"),"Annulation d'un trajet", "Voulez-vous vraiment annuler ce trajet ?");