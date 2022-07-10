let inputStartDay = $("input[name='start-day']");
let inputEndDay = $("input[name='end-day']");

fill_with_same(inputStartDay,inputEndDay,true);
fill_with_same(inputEndDay,inputStartDay,false);

function fill_with_same(src,dst,startToEnd){
    src.change(function (){
        if((dst.val()<=$(this).val() && startToEnd) || (dst.val()>=$(this).val() && !startToEnd)){
            dst.val($(this).val());
        }
    });
}