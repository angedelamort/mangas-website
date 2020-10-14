
$( document ).ready(function() {
    $('#addSeries').click(async function() {
        $('#modalAddSeries').modal({
            onApprove: async function() {
                const body = JSON.stringify({
                    name: $("input[name='series']").val(),
                });
                let result = await fetch(`/api/series`, {
                    method: "POST",
                    headers: { "Content-type": "application/json; charset=UTF-8" },
                    body: body
                });

                if (result.ok) {
                    let json = await result.json();
                    document.location.replace(json.data.uri);
                } else {
                    console.log("error!")
                }
            }
        }).modal('show');
    });
});

function onEdit(id, title) {
    let $modal = $('#modalEditSeries');
    let $input = $modal.find('input[name=series]');
    $input.val(title);
    $modal.modal({
        onApprove: async function() {
            let result = await fetch(`/api/series/${id}`, {
                method: "PATCH",
                headers: { "Content-type": "application/json; charset=UTF-8" },
                body: JSON.stringify({title: $input.val()})
            });
            if (result.ok) {
                document.location.reload();
            }
        }
    }).modal('show');
}

function onDelete(id, title) {
    let $modal = $('#modalDeleteSeries');
    $modal.find('.content').text(`Are you sure you want to delete the series ${title}? (Volume(s) won't be deleted)`);
    $modal.modal({
        onApprove: async function() {
            let result = await fetch(`/api/series/${id}`, {method: "DELETE"});
            if (result.ok) {
                document.location.reload();
            }
        }
    }).modal('show');
}