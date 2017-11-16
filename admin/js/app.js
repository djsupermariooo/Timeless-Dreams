window.onload = function () {

    for (var i in photos) {
        var category = photos[i].category;
        var filename = photos[i].filename;
        var image = document.createElement("img");
        image.src = '../../images/uploads/' + filename;
        image.setAttribute("class", category);
        document.getElementById("portfolio").appendChild(image);
    }
};