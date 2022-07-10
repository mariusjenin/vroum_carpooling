function add_mail_to_list(mail, input, list) {
    let error = $('.error_add_mail');
    if (mail.length > 0) {
        if (validateEmail(mail)) {
            let html = `<div class="mail_friend_list py-2 pl-3 pr-5 m-1 d-flex flex-row align-items-center justify-content-end">\n
                                        <input type="hidden" value="${mail}" name="users_list[]">
                                        ${mail}
                                        <div onclick='remove_mail_from_list($(this).parent());' class="btn_remove_mail_friend_list">\n
                                             <img class="mw-100 mh-100" src="/img/icons/remove.png"/>\n
                                        </div>\n
                                    </div>`;
            list.append(html).hide().fadeIn("500");
            error.removeClass('d-block');
            error.addClass('d-none');
            $(input).val('');
        } else {
            error.removeClass('d-none');
            error.addClass('d-block');
        }
    }
}

function remove_mail_from_list(elem) {
    elem.fadeOut("500", function () {
        $(this).remove();
    });
}

function validateEmail(email) {
    const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}


modal_post_button($(".modal_delete_user_list"),"Suppression d'une liste d'utilisateurs", "Voulez-vous vraiment supprimer cette liste d'utilisateurs ?");