let addedOnce = false;

$( document ).ready(function() {
    
    $('#anilistUpdate').click(function() {
        $('#modalScrapper').modal('show');
    });

    let searchFromScrapper = async function(scrapperId) {
        const text = $('#textSearch').val();
        let response = await fetch(`/api/scrapper/${scrapperId}/${text}`);
        if (response.ok) {
            $('.scrolling.content').html(await response.text());
        }
        // TODO: else -> display error in content!
    };

    $('#assignSearchAniList').click(async function() {
        await searchFromScrapper('anilist');
    });

    $('#assignSearchAnn').click(async function() {
        await searchFromScrapper('ann');
    });

    $('#addVolume').click(function() {
        InitAddModal(true);
        addedOnce = false;
        OpenAddVolume();
    });

    $('#editSeries').click(function() {
        // fill the values of the modal? probably  write it using javascript, will be a lot simpler than fetching all manually.
        $('#modalEditSeries').modal({
            onApprove: async function() {
                let $form = $('#modalEditSeries').find('form');
                let json = $form.serializeArray().reduce(function (obj, item) {
                    obj[item.name] = item.value;
                    return obj;
                }, {});
                let result = await fetch(`/api/series/${json.id}`, {
                    method: "PATCH",
                    headers: { "Content-type": "application/json; charset=UTF-8" },
                    body: JSON.stringify(json)
                });
                if (result.ok) {
                    document.location.reload();
                } else {
                    // TODO: write an error message somewhere in the page.
                    console.log('error updating!');
                }
            }
        }).modal('show');
        // TODO: show an edit button with a form
    });
});

function InitAddModal(isAdd) {
    let $modal = $('#modalAddVolume');
    if (isAdd) {
        $modal.find('.header').text('Add a new Volume');
        $modal.find('.fluid.checkbox').show();
        $modal.find('.positive.button').text("Add");
    } else {
        $modal.find('.header').text('Update Volume')
        $modal.find('.fluid.checkbox').hide();
        $modal.find('.positive.button').text("Update");
    }
}

function OpenAddVolume() {
    $('#modalAddVolume').modal({
        onApprove: async function() {
            const id = $('#series').data('series-id');
            const body = JSON.stringify({
                isbn: $("input[name='isbn']").val(),
                volume: $("input[name='volume']").val(),
                lang: $("input[name='lang']").val()
            });
            let result = await fetch(`/api/series/${id}/volume`, {
                method: "POST",
                headers: {
                    "Content-type": "application/json; charset=UTF-8"
                },
                body: body
            });
            if (result.ok) {
                if ($('input[name="addAgain"]').prop("checked")) {
                    // need to wait a frame in order to re-open the same modal.
                    setTimeout(function() {OpenAddVolume();}, 1);
                } else {
                    document.location.reload();
                }
            } else {
                console.log("error!")
            }
        },
        onDeny: function () {
            if (addedOnce) {
                document.location.reload();
            }
        }
    }).modal('show');
}

async function onSelectedItem(scrapperId, itemId) {
    const seriesId = $('#series').data('series-id');
    document.location.replace(`/scrapper/3WayMerge?scrapperId=${scrapperId}&resourceId=${itemId}&seriesId=${seriesId}`);
}

function onDelete(isbn, volume) {
    let $modal = $('#modalDeleteVolume');
    $modal.find('.content').text(`Are you sure you want to delete the volume ${volume}?`);
    $modal.modal({
        onApprove: async function() {
            let result = await fetch(`/api/volume/${isbn}`, {method: "DELETE"});
            if (result.ok) {
                document.location.reload();
            }
        }
    }).modal('show');
}

function onEdit(isbn, volume, lang) {
    InitAddModal(false);
    $("input[name='isbn']").val(isbn);
    $("input[name='volume']").val(volume);
    $("input[name='lang']").val(lang);

    $('#modalAddVolume').modal({
        onApprove: async function() {
            const body = JSON.stringify({
                isbn:   $("input[name='isbn']").val(),
                volume: $("input[name='volume']").val(),
                lang:   $("input[name='lang']").val()
            });
            let result = await fetch(`/api/volume/${isbn}`, {
                method: "PATCH",
                headers: { "Content-type": "application/json; charset=UTF-8" },
                body: body
            });

            if (result.ok) {
                document.location.reload();
            } else {
                console.log("error!")
            }
        }
    }).modal('show');
}
