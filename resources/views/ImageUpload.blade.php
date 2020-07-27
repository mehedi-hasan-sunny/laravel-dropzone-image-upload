@extends('MainLayout')
@section('content')
    <div class="container">
        <div class="mt-5 d-inline-flex w-100 justify-content-around">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
                Upload
            </button>
            <div class="pl-3 w-100">
                <input type="text" class="form-control" id="search" name="search" placeholder="Search image..."/>
            </div>
        </div>
        <div class="row mt-5" id="image-list">

        </div>

        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Upload image</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body w-100">
                        <form class="needs-validation" novalidate action="{{route('image.upload')}}" method="POST"
                              enctype="multipart/form-data">
                            <div class="form-group">
                                <div class="dropzone" id="myDropzone"></div>
                                <div class="invalid-feedback" id="image-error-message">
                                    Please choose an image.
                                </div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="title">Title</label>
                                <input type="text" class="form-control" id="title" name="title"
                                       placeholder="Enter title" required/>
                                <div class="invalid-feedback">
                                    Please provide a image title.
                                </div>
                            </div>
                            <input class="btn btn-success" id="upload" type="submit" value="Upload">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        Dropzone.autoDiscover = false;
        var isFirstTry = true;
        var myDropzone = $("#myDropzone").dropzone({
            url: "{{route('image.upload')}}",
            parallelUploads: 100,
            thumbnailWidth: 400,
            thumbnailHeight: null,
            maxFiles: 1,
            maxFilesize: 5, // MB
            autoProcessQueue: false,
            acceptedFiles: ".png",
            addRemoveLinks: true,
            init: function () {
                this.on("maxfilesexceeded", function (file) {
                    this.removeAllFiles();
                    this.addFile(file);
                });
                let instance = this; // Makes sure that 'this' is understood inside the functions below.

                // for Dropzone to process the queue (instead of default form behavior):


                this.on("addedfile", function (file) {
                    $('#image-error-message').text('Please choose an image.').hide();
                });

                $('#upload').click(function (e) {

                    let form = $("form")
                    e.preventDefault();
                    e.stopPropagation();
                    form.addClass('was-validated');

                    if (instance.getQueuedFiles().length == 0) {
                        $('#image-error-message').show();
                    }
                    if (instance.getQueuedFiles().length > 0 && form[0].checkValidity() === true) {
                        instance.processQueue();
                    }

                });

            },
            sending: function (file, xhr, formData) {
                formData.append("title", $("#title").val());
                formData.append("_token", "{{ csrf_token() }}");
            },
            removedfile: function (file) {
                file.previewElement.remove();
                // if($("form").hasClass('was-validated')){
                    $('#image-error-message').text('Please choose an image.').show();
                // }
            },
            success: function (file, response) {
                console.log(response);
                myDropzone[0].dropzone.removeAllFiles();
                $("#title").val('');
                $('#image-error-message').hide();
                $('form').removeClass('was-validated');
                let imageData = response.data
                $('#image-list').prepend(
                    imageContent(imageData)
                )
            },
            error: function (file, errorMessage, xhr) {
                file.status = Dropzone.QUEUED;
                if (errorMessage && errorMessage.file) {
                    $('#image-error-message').text(errorMessage.file).show();
                }
            },
        });
        $("#exampleModal").on('hidden.bs.modal', function () {
            $(this).data('modal', null);
            myDropzone[0].dropzone.removeAllFiles();
            $("#title").val('');
            $('form').removeClass('was-validated');
        });

        $.ajax({
            type: 'GET',
            url: '{{route('image.list')}}',
            success: function (response) {
                let imageList = response.data.reverse();
                imageList.forEach(image => {
                    console.log(image)
                    $('#image-list').append(
                        imageContent(image)
                    )
                });
                console.log(response.data)
            },
            error: function () {
                console.log(data);
            }
        });

        $('#image-list').delegate('button','click',function(e) {

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                removeImage(e.target.parentNode.getAttribute("data-url"));

                if (result.value) {
                    Swal.fire(
                        'Deleted!',
                        'Your file has been deleted.',
                        'success'
                    )
                }
            })
            // console.log(e.target.parentNode.getAttribute("data-url"))
        });
        $(document).on('click', '[data-toggle="lightbox"]', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox();
        });

        function imageContent(image) {
            return `<div class="col-2 text-center mb-3" data-url="${image.url}">
                        <a href="${image.url}" data-toggle="lightbox">
                            <div class="image-container d-inline-flex align-items-center">
                               <img class="img-fluid img-thumbnail mh-100 img-fluid" src="${image.url}" alt="${image.title}">
                            </div>
                        </a>
                        <div class="image-title-wrapper mb-2">
                            <div class="image-title ${image.title.length > 20 ? 'animated-title' : ''}" data-title="${image.title}">${image.title}</div>
                        </div>
                        <button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Remove</button>
                    </div>`
        }

        function removeImage(imageUrl) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: 'POST',
                data:{
                    url: imageUrl
                },
                url: '{{route('image.remove')}}',
                success: function (response) {

                    $(`#image-list div.col-2.text-center[data-url='${imageUrl}']`).remove();
                    console.log(response)
                },
                error: function () {
                    console.log(data);
                }
            });
        }


        $("#search").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#image-list .image-title").filter(function(e) {
                console.log($(this).closest('.col-2'));
                $(this).closest('.col-2').toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });



    </script>
@stop
