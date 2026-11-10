<form action="{{route('currencies.store')}}" method="POST">
    @csrf
    <div class="row">
        <div class="col-6 mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Currency Name" required>
        </div>
        <div class="col-6 mb-3">
            <label for="code" class="form-label">Code</label>
            <input type="text" class="form-control" id="code" name="code" placeholder="Currency Code" required>
        </div>
        <div class="col-6 mb-3">
            <label for="symbol" class="form-label">Symbol</label>
            <input type="text" class="form-control" id="symbol" name="symbol" placeholder="Currency Symbol" required>
        </div>
        <div class="col-6 mb-3">
            <label for="exchange_rate" class="form-label">Exchange Rate</label>
            <input type="number" step="any" class="form-control" id="exchange_rate" name="exchange_rate" value="1" placeholder="Exchange Rate" required>
        </div>
        <div class="col-6 mb-3">
            <label for="position" class="form-label">Position</label>
            <select class="form-control" id="position" name="position">
                <option value="1" selected>Prefix (e.g. {{ \App\Helpers\CurrencyHelper::getSymbol() }}100.00)</option>
                <option value="2">Suffix (e.g. 100.00{{ \App\Helpers\CurrencyHelper::getSymbol() }})</option>
            </select>
        </div>
        <div class="col-6 mb-3">
            <div class="row">
                <div class="col-12">
                    <label class="form-check-label" for="is_active">Is Active</label>
                </div>
                <div class="col-12 text-center">
                    <div class="form-check form-switch">
                        <input class="form-check-input switch-size-lg" type="checkbox" checked role="switch" id="is_active" name="is_active" style="width: 50px; height: 25px;">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-start">
        <button type="submit" class="btn btn-primary">Create</button>
    </div>
</form>