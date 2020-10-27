
$( document ).ready(function() {
    $('#addSeries').click(async function() {
        let $modal = $('#modalAddSeries');
        $modal.modal({
            onApprove: async function() {
                const body = JSON.stringify({
                    title: $modal.find("input[name=title]").val(),
                    short_name: $modal.find("input[name=short_name]").val()
                });
                let result = await fetch(`/api/series`, {
                    method: "POST",
                    headers: { "Content-type": "application/json; charset=UTF-8" },
                    body: body
                });

                if (result.ok) {
                    let json = await result.json();
                    document.location.replace(json.redirectUrl);
                } else {
                    console.log("error!")
                }
            }
        }).modal('show');
    });

    $('#filter').keyup(function() {
        const text = $(this).val();
        $('.card').each(function() {
            const title = $(this).find('.small.header').text();
            console.log(`${title} indexof ${text} = ${title.toLowerCase().indexOf(text.toLowerCase()) >= 0}`);
            $(this).toggle(title.toLowerCase().indexOf(text.toLowerCase()) >= 0);
        });
    });
});

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