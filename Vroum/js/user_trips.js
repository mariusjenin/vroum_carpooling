//Le bouton annuler le trajet trigger maintenant une fenêtre modale pour confirmer
let btns_cancel_participation = $(".modal_cancel_participation_my_trips");
modal_post_button(btns_cancel_participation,"Annulation de la participation à un trajet", "Voulez-vous vraiment annuler votre participation à ce trajet ?");