$(function () {
    let slider_range = $("#slider-range");
    slider_range.slider({
        range: true,
        min: 0,
        max: 100,
        values: [0, 100],
        slide: function (event, ui) {
            $("#amount").val("€" + ui.values[0] + " - €" + ui.values[1]);
        }
    });
    $("#amount").val("€" + slider_range.slider("values", 0) +
        " - €" + slider_range.slider("values", 1));
});

function loading_search_trip(affich) {
    let loading = $(".loading_search_trip");
    if (!affich) {
        loading.addClass("d-none");
        $(".loading_search_trip").removeClass("d-flex");
    } else {
        loading.removeClass("d-none");
        loading.addClass("d-flex");
    }
}

function no_result(affich) {
    let no_result = $(".no_result_search_trip");
    if (!affich) {
        no_result.addClass("d-none");
    } else {
        no_result.removeClass("d-none");
    }
}

function done_search_trip(trips) {
    let list = document.getElementsByClassName('trip_list')[0];
    let url_consult = list.dataset.url_consult;

    //On retire le loading de l'affichage
    loading_search_trip(false);

    let html = "";
    if (trips.length === 0) {
        //On affiche le "Pas de résultats"
        no_result(true);
    } else {
        //On génère le html de tous les résultats
        for (key in trips){
            let trip = trips[key];
            let aproximative = false;

            let stars = "";
            if(trip.stars>0){
                for (let j = 0; j < 5; j++) {
                    if (j < trip.stars) {
                        stars += '<i class="bi bi-star-fill m-1 star_profile"></i>';
                    } else {
                        stars += '<i class="bi bi-star m-1 star_profile"></i>';
                    }
                }
            } else {
                for (let j = 0; j < 5; j++) {
                    stars += '<i class="bi bi-star m-1 disabled_star star_profile"></i>';
                }
            }

            let lock = trip.prive ? '<img class="lock_picture mr-2" src="../img/icons/lock.png">' : '';
            
            let sexe = trip.sexe ? 'Homme' : 'Femme';

            let via = "";
            if ("interCity" in trip){
                via = '<div class="p-2 "> via   <span class="font-weight-bold">';

                let interCity = trip.interCity;
                for (let j = 0; j < interCity.length; j++) {
                    via += interCity[j];
                    if (j < interCity.length-1) {
                        via += ', ';
                    }
                }
                via += '</span></div>';
            }

            let aproximativeA = "";
            if (trip.arrivéeAproximative){
                aproximativeA += "aproximative";
                aproximative = true;
            }
            
            let tripHtml =  `
                <a href="${url_consult + trip.idTrajet}" class="d-flex flex-column justify-content-around align-items-center search_trip_result p-3 mb-3">
                    <div class="row w-100">
                        <div class="col-12 col-lg-5 pl-0">
                            <div class="d-flex flex-row align-items-center">
                                <img class="picture_driver_trip" src="${trip.photo}">
                                <div class="font-weight-bold d-flex flex-column justify-content-around align-items-center w-100">
                                    ${trip.prenom}
                                    <div>
                                        ${stars}
                                    </div>
                                    ${sexe}
                                </div>
                            </div>
                        </div>
                        <div class="col-8 col-lg-6 d-flex flex-column align-items-center justify-content-around">
                            <div class="h-100 d-flex flex-row align-items-center justify-content-center flex-wrap">
                                <div class="p-2">
                                    <span class="text-center">
                                        Trajet de
                                    </span>
                                    <span class="font-weight-bold">
                                        ${trip.villeD}
                                    </span>
                                </div>
                                <div class="p-2">
                                    <span class="text-center">
                                        jusqu'à
                                    </span>
                                    <span class="font-weight-bold">
                                        ${trip.villeA}
                                    </span>
                                </div>
                                ${via}
                            </div>
                            <div class="h-100 d-flex flex-row align-items-center justify-content-center flex-wrap">
                                <div class="p-2">
                                    <span class="text-center">
                                        Départ le
                                    </span>
                                    <span class="font-weight-bold">
                                        ${trip.dateD}
                                    </span>
                                    <span class="text-center">
                                        à
                                    </span>
                                    <span class="font-weight-bold">
                                        ${trip.heureD}
                                    </span>
                                </div>
                                <div class="p-2">
                                    <span class="text-center">
                                        Arivée le
                                    </span>
                                    <span class="font-weight-bold ${aproximativeA}">
                                       ${trip.dateA}
                                    </span>
                                    <span>
                                        à
                                    </span>
                                    <span class="font-weight-bold ${aproximativeA}">
                                        ${trip.heureA}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-4 col-lg-1">
                            <div class="h-100 d-flex flex-row align-items-center justify-content-end mr-0 mr-lg-3">
                                ${lock}
                                <div class="font-weight-bold price_result">${trip.prix}€</div>
                            </div>
                        </div>
                    </div>
                </a>`;

            if (aproximative){html = html + tripHtml;}
            else {html = tripHtml + html;}
        }
    }
    list.innerHTML = html;


}
