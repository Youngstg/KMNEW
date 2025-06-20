@extends('layouts.admin.app')

@section('title', 'Edit Footer')

@section('js')
<script src="{{ asset('lte/plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('plugins/tinymce/js/tinymce/tinymce.min.js') }}"></script>
@endsection

<style>
    #delete {
        border: none;
        border-radius: 5px !important;
        padding: 12px !important;
    }

    #add_no_cp,
    #add_sosmed {
        display: flex !important;
        gap: 8px;
        align-items: center !important;
        margin-top: 8px !important;
    }

    #add_sosmed select {
        width: 10rem;
    }
</style>

@section('script')
<script>
    function previewNewImage(event) {
        var reader = new FileReader();
        reader.onload = function () {
            var output = document.getElementById('newImage');
            output.src = reader.result;
            document.getElementById('newImagePreview').style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    function addInput(field) {
        var maxNoCp = 3;
        var maxSosmed = 5;

        if (field === 'no_cp') {
            var currentNoCpCount = document.querySelectorAll('input[name^="no_cp"]').length;
            if (currentNoCpCount < maxNoCp) {
                var container = document.createElement('div');
                container.id = 'add_no_cp';
                container.innerHTML = '<input name="no_cp[]" class="form-control @error('no_cp') is-invalid @enderror" placeholder="Masukkan no cp" type="tel"> <button type="button" id="delete" class="bg-secondary" onclick="removeInput(this)"><i class="fa-regular fa-trash-can"></i></button>';
                document.getElementById('no_cp-container').appendChild(container);
            } else {
                alert('Maksimal 3 nomor CP.');
            }
        } else if (field === 'sosmed') {
            var currentSosmedCount = document.querySelectorAll('select[name^="sosmed"]').length;
            console.log('Current Sosmed Count:', currentSosmedCount);

            if (currentSosmedCount < maxSosmed) {
                console.log('Adding new input fields');
                var container = document.createElement('div');
                container.id = 'add_sosmed';
                container.innerHTML = `
        <select name="sosmed[${currentSosmedCount}][icon]" class="form-control">
            <option value="">Pilih icon</option>
            <option value="facebook">Facebook</option>
            <option value="twitter">Twitter</option>
            <option value="instagram">Instagram</option>
            <option value="whatsapp">Whatsapp</option>
            <option value="youtube">Youtube</option>
            <option value="spotify">Spotify</option>
            <option value="telegram">Telegram</option>
            <option value="tiktok">Tik Tok</option>
        </select>
        <input type="url" name="sosmed[${currentSosmedCount}][link]" placeholder="Masukkan Link Sosmed" class="form-control">
        <button type="button" id="delete" class="bg-secondary" onclick="removeInput(this)"><i class="fa-regular fa-trash-can"></i></button>
    `;
                document.getElementById('sosmed-container').appendChild(container);
            } else {
                alert('Maksimal 5 sosial media.');
            }

        }
    }

    function removeInput(button) {
        button.parentElement.remove();
    }
</script>
@endsection

@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <!-- general form elements -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Edit Footer</h3>
                    </div>
                    <!-- /.card-header -->
                    <!-- form start -->
                    <form method="POST" action="{{ route('admin.footer.update', $footer->id) }}"
                        enctype='multipart/form-data'>
                        @csrf
                        @method('PUT')
                        <div class="card-body" style="overflow-x:overlay;">
                            <div class="form-group" id="sosmed-container">
                                <label for="sosmed">Link Sosmed</label>
                                @foreach($footer->sosmed as $key => $sosmed)
                                    <div id="add_sosmed">
                                        <select name="sosmed[{{ $key }}][icon]"
                                            class="form-control @error('sosmed[{{ $key }}][icon]') is-invalid @enderror">
                                            <option value="">Pilih icon</option>
                                            <option value="facebook" {{ $sosmed['icon'] == 'facebook' ? 'selected' : '' }}>
                                                Facebook</option>
                                            <option value="twitter" {{ $sosmed['icon'] == 'twitter' ? 'selected' : '' }}>
                                                Twitter</option>
                                            <option value="instagram" {{ $sosmed['icon'] == 'instagram' ? 'selected' : '' }}>
                                                Instagram</option>
                                            <option value="whatsapp" {{ $sosmed['icon'] == 'whatsapp' ? 'selected' : '' }}>
                                                Whatsapp</option>
                                            <option value="youtube" {{ $sosmed['icon'] == 'youtube' ? 'selected' : '' }}>
                                                Youtube</option>
                                            <option value="spotify" {{ $sosmed['icon'] == 'spotify' ? 'selected' : '' }}>
                                                Spotify</option>
                                            <option value="telegram" {{ $sosmed['icon'] == 'telegram' ? 'selected' : '' }}>
                                                Telegram</option>
                                            <option value="tiktok" {{ $sosmed['icon'] == 'tiktok' ? 'selected' : '' }}>
                                                Tik Tok</option>
                                        </select>

                                        <input name="sosmed[{{ $key }}][link]" value="{{ $sosmed['link'] }}"
                                            class="form-control @error('sosmed[{{ $key }}][link]') is-invalid @enderror"
                                            placeholder="Masukkan link sosmed" type="link">
                                        <button type="button" id="delete" class="bg-secondary"
                                            onclick="removeInput(this)"><i class="fa-regular fa-trash-can"></i></button>
                                    </div>
                                @endforeach
                                <button type="button" class="mt-1 bg-transparent text-primary border-0"
                                    onclick="addInput('sosmed')">
                                    + Tambah sosial media</button>
                                @error('sosmed')
                                    <div id="desk-error" class="error invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group" id="no_cp-container">
                                <label for="no_cp">No. Cp</label>
                                @foreach($footer->no_cp as $cp)
                                    <div id="add_no_cp">

                                        <input name="no_cp[]" value="{{ $cp }}"
                                            class="form-control @error('no_cp') is-invalid @enderror"
                                            placeholder="Masukkan no cp" type="tel">
                                        <button type="button" id="delete" class="bg-secondary"
                                            onclick="removeInput(this)"><i class="fa-regular fa-trash-can"></i></button>
                                    </div>
                                @endforeach
                                <button type="button" class="mt-1 bg-transparent text-primary border-0"
                                    onclick="addInput('no_cp')">+
                                    Tambah
                                    nomor
                                    telepon</button>
                                @error('no_cp')
                                    <div id="desk-error" class="error invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input name="email" value="{{ $footer->email }}"
                                    class="form-control @error('email') is-invalid @enderror" id="email"
                                    placeholder="Masukkan email" type="email">
                                @error('email')
                                    <div id="desk-error" class="error invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="alamat_sekre">Alamat sekre</label>
                                <textarea name="alamat_sekre"
                                    class="form-control custom-txt-area @error('alamat_sekre') is-invalid @enderror"
                                    id="alamat_sekre"
                                    placeholder="Masukkan Alamat Sekre">{{ $footer->alamat_sekre }}</textarea>
                                @error('alamat_sekre')
                                    <div id="desk-error" class="error invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="hak_cipta">Copyright</label>
                                <input name="hak_cipta" value="{{ $footer->hak_cipta }}"
                                    class="form-control @error('hak_cipta') is-invalid @enderror" id="hak_cipta"
                                    placeholder="Masukkan Hak Cipta" type="text" required>
                                @error('hak_cipta')
                                    <div id="desk-error" class="error invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>
</section>
@endsection