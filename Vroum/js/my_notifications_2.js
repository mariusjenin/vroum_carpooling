$('.block_notif').hover(function (e) {
    $(this).find('.img_new_notification').fadeOut(300,function () {
        $(this).remove();
    });

    $(this).unbind();
});



modal_post_button($(".modal_delete_notification"),"Suppression d'une notification", "Voulez-vous vraiment supprimer cette notification ?");
modal_post_button($(".modal_delete_all_notifications"),"Suppression de Notifications", "Voulez-vous vraiment supprimer toutes les notifications provenant de cet utilisateur ?");
