<div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
    <table class="table table-sm table-striped table-hover">
        <thead>
            <tr>
                <th>Nama Cabang</th>
                <th>Alamat</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($cabang as $c)
                <tr>
                    <form action="{{ route('branch.edit') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $c->id }}">
                        <td>
                            <input type="text" name="cabang" class="form-control form-control-sm" value="{{ $c->cabang }}" required>
                        </td>
                        <td>
                            <input type="text" name="alamat" class="form-control form-control-sm" value="{{ $c->alamat }}" required>
                        </td>
                        <td class="text-end">
                            <button type="submit" class="btn btn-sm btn-warning">
                                <i class="bi bi-save"></i>
                            </button>
                    </form>
                    <form action="{{ route('branch.delete', $c->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Menghapus Cabang akan menghapus seluruh Data Ruang nya, Apakah kamu Yakin?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted">Belum ada data cabang.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($errors->any())
    <div class="alert alert-danger mt-3">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
