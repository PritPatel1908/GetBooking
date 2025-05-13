@extends('layouts.admin')

@section('title', 'Upload Ground Images')

@section('content')
<main class="flex-1 overflow-y-auto p-4 page-transition">
    <div class="mb-4">
        <a href="{{ route('admin.grounds.show', $ground->id) }}" class="text-indigo-600 hover:text-indigo-800 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to Ground Details
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="flex items-center p-4 border-b">
            <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center mr-3">
                <i class="fas fa-image text-indigo-500"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-800">Upload Images for {{ $ground->name }}</h1>
                <p class="text-sm text-gray-500">Add photos to showcase this ground to potential customers</p>
            </div>
        </div>

        <div class="p-6">
            <form id="upload-form" method="POST" action="{{ route('admin.ground_images.upload') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="ground_id" value="{{ $ground->id }}">

                <div class="mb-6">
                    <label for="images" class="block text-sm font-medium text-gray-700 mb-2">Select Images</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-indigo-500 transition-colors cursor-pointer relative" id="drop-area">
                        <input type="file" name="images[]" id="images" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="space-y-2">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400"></i>
                            <p class="text-sm text-gray-500">
                                Drag and drop your images here, or <span class="text-indigo-600 font-medium">click to browse</span>
                            </p>
                            <p class="text-xs text-gray-400">
                                JPG, PNG, or GIF files up to 2MB each
                            </p>
                        </div>
                    </div>
                </div>

                <div id="preview-container" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
                    <!-- Image previews will appear here -->
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('admin.grounds.show', $ground->id) }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3">
                        Cancel
                    </a>
                    <button type="submit" id="upload-button" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" disabled>
                        Upload Images
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Current Images Section -->
    @if(isset($ground->images) && count($ground->images) > 0)
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="text-lg font-medium text-gray-800">Current Images</h2>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($ground->images as $image)
                <div class="bg-gray-100 rounded-lg overflow-hidden h-36 relative group">
                    <img src="{{ asset($image->image_path) }}" alt="Ground Image" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center space-x-2">
                        <a href="{{ asset($image->image_path) }}" target="_blank" class="p-2 bg-white rounded-full text-gray-700 hover:text-indigo-600 transition-colors">
                            <i class="fas fa-expand"></i>
                        </a>
                        <button type="button" class="p-2 bg-white rounded-full text-gray-700 hover:text-red-600 transition-colors delete-image-btn" data-id="{{ $image->id }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</main>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('upload-form');
    const fileInput = document.getElementById('images');
    const previewContainer = document.getElementById('preview-container');
    const uploadButton = document.getElementById('upload-button');
    const dropArea = document.getElementById('drop-area');

    // Handle file selection
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    // Handle drag and drop
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
        dropArea.classList.add('border-indigo-500', 'bg-indigo-50');
    }

    function unhighlight() {
        dropArea.classList.remove('border-indigo-500', 'bg-indigo-50');
    }

    dropArea.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }, false);

    function handleFiles(files) {
        previewContainer.innerHTML = '';

        if (files.length > 0) {
            uploadButton.disabled = false;

            Array.from(files).forEach(file => {
                if (!file.type.match('image.*')) return;

                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('div');
                    preview.className = 'bg-gray-100 rounded-lg overflow-hidden h-36 relative group';
                    preview.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black bg-opacity-30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <span class="text-white text-xs bg-gray-900 bg-opacity-75 px-2 py-1 rounded">${file.name}</span>
                        </div>
                    `;
                    previewContainer.appendChild(preview);
                };
                reader.readAsDataURL(file);
            });
        } else {
            uploadButton.disabled = true;
        }
    }

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (fileInput.files.length === 0) {
            alert('Please select at least one image to upload.');
            return;
        }

        const formData = new FormData(this);
        uploadButton.disabled = true;
        uploadButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Uploading...';

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Redirect to ground view page without alert
                // The toast notification will show after redirect due to session flash
                window.location.href = "{{ route('admin.grounds.show', $ground->id) }}";
            } else {
                alert('Error: ' + (data.message || 'Failed to upload images.'));
                uploadButton.disabled = false;
                uploadButton.innerHTML = 'Upload Images';
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            alert('An error occurred during upload. Please try again.');
            uploadButton.disabled = false;
            uploadButton.innerHTML = 'Upload Images';
        });
    });

    // Handle image deletion
    const deleteButtons = document.querySelectorAll('.delete-image-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this image?')) {
                const imageId = this.getAttribute('data-id');

                fetch(`/admin/ground-images/${imageId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Remove the image container from the UI
                        this.closest('.bg-gray-100').remove();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete image.'));
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    alert('An error occurred while deleting the image.');
                });
            }
        });
    });
});
</script>
@endsection
