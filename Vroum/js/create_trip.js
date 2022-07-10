function add_city_to_list(city,input,list){
    if(city.length>0){
        let html=`<div class="intermediate_city py-2 pl-3 pr-5 m-1 d-flex flex-row align-items-center justify-content-end">\n
                                        <input type="hidden" value="${city}" name="villeInter[]">
                                        ${city}
                                        <div onclick='remove_city_from_list($(this).parent());' class="btn_remove_intermediate_city">\n
                                             <img class="mw-100 mh-100" src="../img/icons/remove.png"/>\n
                                        </div>\n
                                    </div>`;
        list.append(html).hide().fadeIn("500");
    }
    $(input).val('');
}

function remove_city_from_list(elem){
    elem.fadeOut("500", function() {
        $(this).remove();
    });
}