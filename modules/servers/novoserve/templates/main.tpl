{if $success}<div class="alert alert-success" role="alert">{$success}</div>{/if}

<div class="card text-left">
    <div class="card-header">
        Server Details
    </div>
    <div class="card-body">
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <div class="input-group-text">Tag</div>
            </div>
            <input type="text" class="form-control" value="{$serverTag}" readonly>
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" onclick="copyToClipboard('{$serverTag}', this)">Copy</button>
            </div>
        </div>
        {if $serverTag != $serverHostname && $serverHostname != ''}
        <div class="input-group">
            <div class="input-group-prepend">
                <div class="input-group-text">Hostname</div>
            </div>
            <input type="text" class="form-control" value="{$serverHostname}" readonly>
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" onclick="copyToClipboard('{$serverHostname}', this)">Copy</button>
            </div>
        </div>
        {/if}
    </div>
</div>

<div class="card text-left">
    <div class="card-header">
        Bandwidth
    </div>
    <div class="card-body">
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <div class="input-group-text">Period</div>
            </div>
            <input type="text" class="form-control" value="{$trafficUsage.dateTimeFrom} - {$trafficUsage.dateTimeUntil}" readonly>
        </div>
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <div class="input-group-text">Usage Outgoing</div>
            </div>
            <input type="text" class="form-control" value="{$trafficUsage.usage} TB" readonly>
        </div>
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <div class="input-group-text">Usage Incoming</div>
            </div>
            <input type="text" class="form-control" value="{$trafficUsage.download} TB" readonly>
        </div>
        <img class="img-fluid" src="{$bandwidthGraph}">
    </div>
</div>

<div class="card text-left">
    <div class="card-header">
        Power Management (server is currently '{$powerStatus}')
    </div>
    <div class="card-body">
        <form method="POST">
            <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to proceed?')" name="poweron">Power On</button>
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to proceed?')" name="reset">Reset</button>
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to proceed?')" name="poweroff">Power Off</button>
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to proceed?')" name="coldboot">Cold Boot</button>
        </form>
    </div>
</div>

<div class="card text-left">
    <div class="card-header">
        Remote Access
    </div>
    <div class="card-body">
        <a href="{$ipmiLink}" target="_blank" class="btn btn-primary">IPMI</a>
    </div>
</div>

<script>
    if (document.querySelector('#domain').childNodes[1]) {
        document.querySelector('#domain').childNodes[1].remove();
    }
    if (document.getElementsByClassName('btn btn-block btn-danger')[0]) {
        document.getElementsByClassName('btn btn-block btn-danger')[0].remove();
    }
</script>
<script type="text/javascript">
    async function copyToClipboard(text, button) {
        if (!navigator || !navigator.clipboard || !navigator.clipboard.writeText){
            return Promise.reject('The Clipboard API is not available.');
        }

        button.innerHTML = 'Copied!';
        await navigator.clipboard.writeText(text);
        await new Promise(r => setTimeout(r, 2000));
        button.innerHTML = 'Copy';
    }
</script>
