console.log('JavaScript loaded!');

document.addEventListener("DOMContentLoaded", function() {
    compare = false;
    document.querySelectorAll("#product-table tbody tr").forEach(row => {
        row.addEventListener("click", function() {
            const productId = this.getAttribute("data-product-id");
            handleRowEvent(productId);
        });
    });
});

function handleRowEvent(productId) {
    if(compare == true) {
        showCompare(productId)
    } else {
        showProductDetail(productId);
    }
}

function uploadJson() {
    $('#content').html('Loading...');

    var form = document.getElementById('upload-form');
    var formData = new FormData(form);
    var uploadUrl = form.getAttribute('data-upload-url');

    $.ajax({
        url: uploadUrl,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (data) {
            $('#content').html(data);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Error uploading file:', textStatus, errorThrown);
            $('#content').html('Something went wrong.');
        }
    });
}

function showProductDetail(productId) {
    $('#detail-response').html('Loading...');
    document.querySelector(".table-container").style.display = "none";
    document.getElementById("product-detail").style.display = "block";
    var productDetailUrl = document.querySelector(".table-container").getAttribute('data-product-detail-url');

    $.ajax({
        url: productDetailUrl,
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        data: productId,
        processData: false,
        contentType: false,
        success: function (data) {
            $('#detail-response').html(data);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Error uploading file:', textStatus, errorThrown);
            $('#detail-response').html('Something went wrong.');
        }
    });
}

function showCompare(comparedToId) {
    var productId = document.querySelector(".product-detail").getAttribute('data-product-ean');
    var compareUrl = document.getElementById("comparrison-header").getAttribute('data-compare-url');

    $.ajax({
        url: compareUrl,
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        data: JSON.stringify({
            ean: productId,
            compared: comparedToId
        }),
        processData: false,
        contentType: false,
        success: function (data) {
            $('#detail-response').html(data);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Error uploading file:', textStatus, errorThrown);
            $('#detail-response').html('Something went wrong.');
        }
    });

    cancelCompare();
}

function showProductList() {
    document.getElementById("product-detail").style.display = "none";
    document.querySelector(".table-container").style.display = "block";
}

function initCompare() {
    document.getElementById("comparrison-header").style.display = "block";
    document.querySelector(".table-container").style.display = "block";
    compare = true;
}

function cancelCompare() {
    document.getElementById("comparrison-header").style.display = "none";
    document.querySelector(".table-container").style.display = "none";
    compare = false;
}

$(document).ready(function(){
    console.log('Document ready.');
});