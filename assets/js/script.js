
$(document).ready(function(){
    
    //console.log("salut !");

    $(".result").on("click",function(){
        console.log("Lien cliqué");

        // ID(siteResultsProvider)

        var id= $(this).attr("data-linkId");

        // URL (href)

        var url = $(this).attr("href");
        // test 

        //console.log(url);

        if(!id){
            alert("data-linkId non trouvé");
        }

        //+1 dans la bdd

        increaseClicks(id,url);
        /* return false; */ // permet de bloquer la redirection
    });
});

    function increaseClicks(linkId,url){

        $.post("ajax/updateLinkCount.php",{linkId:linkId}).done(function(result){
            if(result !=""){
                alert(result);
                return;
            }
            window.location.href=url;
        });
    }

