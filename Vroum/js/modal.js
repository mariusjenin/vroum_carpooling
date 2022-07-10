function close_modal() {
    $(".modal_window").fadeOut(200,function () {
        $(this).remove();
    });
    $('.content').removeClass('blurred_element');
}

function open_modal(title, text, accept_fct = function () {}) {
    let html_modal = `
        <div class="position-fixed modal_window vh-100 vw-100 background_modal d-flex flex-row justify-content-center align-items-center">
            <div class="modal_block position-relative">
                <div class="btn_hoverable modal_close">
                    <img class="mh-100 mw-100" src="/img/icons/remove.png">
                </div>
                <div class="h3 px-4 py-3 modal_title">
                       ${title}
                </div>
                <div class="modal_content">
                    <div class="row">
                        <div class="col-12 text-center">
                            ${text}
                        </div>
                        <hr class="modal_separator m-4">
                        <div class="col-12 d-flex flex-row justify-content-around align-items-center modal_box_choice">
                            <div class="modal_choice modal_refuse btn_hoverable px-3 py-1">
                                Annuler
                            </div>
                            <div class="modal_choice modal_accept btn_hoverable px-3 py-1">
                                Confirmer
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
    $('.content').addClass('blurred_element');
    $('body').prepend(html_modal).hide().fadeIn("200");

    //Cliquer sur le bouton refuser, sur la croix ou en dehors de la page ferme la modale
    $('.modal_refuse, .modal_close , .background_modal').click(close_modal);

    //Cliquer sur le bouton accepter trigger la fonction associée puis ferme la modale
    $('.modal_accept').click(function () {
        accept_fct();
        close_modal();
    });

    $(".modal_block").click(function(e) {
        //On stoppe la propagation sur le block modal car sinon cliquer sur la modale la fermera
        e.stopPropagation();
    });
}

function modal_post_button(elem,title,text){
    elem.click(function (e) {
        e.stopPropagation();
        e.preventDefault();
        let post_button_url = $(this)[0].dataset.modal_accept;
        let post_button_data_json = $(this)[0].dataset.modal_accept_data;
        let post_button_data_array = [];
        if(post_button_data_json !== undefined){
            post_button_data_array = JSON.parse($(this)[0].dataset.modal_accept_data);
        }
        if(post_button_url !== undefined) {
            let accept = function (){
                post_button(post_button_url,post_button_data_array);
            };
            open_modal(title, text, accept);
        }
    })
}