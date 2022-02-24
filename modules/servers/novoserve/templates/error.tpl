<div class="alert alert-danger" role="alert">{$error}</div>

<script>
    if (document.querySelector('#domain').childNodes[1]) {
        document.querySelector('#domain').childNodes[1].remove();
    }
    if (document.getElementsByClassName('btn btn-block btn-danger')[0]) {
        document.getElementsByClassName('btn btn-block btn-danger')[0].remove();
    }
</script>