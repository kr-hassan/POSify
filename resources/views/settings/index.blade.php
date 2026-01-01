@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Shop Settings</h2>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-shop"></i> Shop Information
                    </h5>
                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                        These details will be printed on all invoices and receipts
                    </p>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="shop_logo" class="form-label">
                                <i class="bi bi-image"></i> Shop Logo
                            </label>
                            <input type="file" 
                                   class="form-control @error('shop_logo') is-invalid @enderror" 
                                   id="shop_logo" 
                                   name="shop_logo" 
                                   accept="image/*"
                                   onchange="previewLogo(this)">
                            @error('shop_logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Upload a logo image (PNG, JPG, GIF). Recommended size: 200x100px. This will appear on the login page.
                            </small>
                            @if($shopLogo)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $shopLogo) }}" alt="Shop Logo" id="logo_preview" style="max-height: 100px; max-width: 200px; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">
                                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeLogo()">
                                        <i class="bi bi-trash"></i> Remove Logo
                                    </button>
                                </div>
                            @else
                                <div class="mt-2">
                                    <img id="logo_preview" src="" alt="Logo Preview" style="max-height: 100px; max-width: 200px; border: 1px solid #ddd; padding: 5px; border-radius: 4px; display: none;">
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="shop_name" class="form-label">
                                Shop Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('shop_name') is-invalid @enderror" 
                                   id="shop_name" 
                                   name="shop_name" 
                                   value="{{ old('shop_name', $shopName) }}" 
                                   required
                                   placeholder="Enter shop/business name">
                            @error('shop_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                This will appear as the header on all invoices and receipts
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="shop_address" class="form-label">Shop Address</label>
                            <textarea class="form-control @error('shop_address') is-invalid @enderror" 
                                      id="shop_address" 
                                      name="shop_address" 
                                      rows="3"
                                      placeholder="Enter shop address">{{ old('shop_address', $shopAddress) }}</textarea>
                            @error('shop_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Full address including street, city, state, zip code
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shop_phone" class="form-label">Phone Number</label>
                                    <input type="text" 
                                           class="form-control @error('shop_phone') is-invalid @enderror" 
                                           id="shop_phone" 
                                           name="shop_phone" 
                                           value="{{ old('shop_phone', $shopPhone) }}"
                                           placeholder="Enter phone number">
                                    @error('shop_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shop_email" class="form-label">Email Address</label>
                                    <input type="email" 
                                           class="form-control @error('shop_email') is-invalid @enderror" 
                                           id="shop_email" 
                                           name="shop_email" 
                                           value="{{ old('shop_email', $shopEmail) }}"
                                           placeholder="Enter email address">
                                    @error('shop_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="footer_message" class="form-label">Footer Message</label>
                            <textarea class="form-control @error('footer_message') is-invalid @enderror" 
                                      id="footer_message" 
                                      name="footer_message" 
                                      rows="2"
                                      placeholder="Enter footer message (e.g., Thank you for your business!)">{{ old('footer_message', $footerMessage) }}</textarea>
                            @error('footer_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                This message will appear at the bottom of all invoices and receipts
                            </small>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">
                            <i class="bi bi-megaphone"></i> Software Company Advertisement
                        </h5>
                        <p class="text-muted mb-3" style="font-size: 0.875rem;">
                            Add your software company information to advertise on invoices
                        </p>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="show_advertisement" value="1" id="showAdvertisement" {{ old('show_advertisement', $showAdvertisement) == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="showAdvertisement">
                                    <strong>Show advertisement on invoices</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Enable this to display your software company information on all invoices and receipts
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="software_company_name" class="form-label">Company Name</label>
                            <input type="text" 
                                   class="form-control @error('software_company_name') is-invalid @enderror" 
                                   id="software_company_name" 
                                   name="software_company_name" 
                                   value="{{ old('software_company_name', $softwareCompanyName) }}"
                                   placeholder="Enter your software company name">
                            @error('software_company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="software_company_website" class="form-label">Website URL</label>
                            <input type="url" 
                                   class="form-control @error('software_company_website') is-invalid @enderror" 
                                   id="software_company_website" 
                                   name="software_company_website" 
                                   value="{{ old('software_company_website', $softwareCompanyWebsite) }}"
                                   placeholder="https://www.yourcompany.com">
                            @error('software_company_website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="software_company_tagline" class="form-label">Tagline / Message</label>
                            <textarea class="form-control @error('software_company_tagline') is-invalid @enderror" 
                                      id="software_company_tagline" 
                                      name="software_company_tagline" 
                                      rows="2"
                                      placeholder="e.g., Powered by Your Company - Professional POS Solutions">{{ old('software_company_tagline', $softwareCompanyTagline) }}</textarea>
                            @error('software_company_tagline')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Short tagline or message to display (e.g., "Powered by...")
                            </small>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="border p-3" style="font-size: 0.85rem;">
                        <div class="text-center border-bottom pb-2 mb-2">
                            <strong id="preview_shop_name">{{ $shopName ?: 'POS SYSTEM' }}</strong>
                        </div>
                        <div id="preview_shop_address" class="text-center mb-2" style="min-height: 20px;">
                            {{ $shopAddress ?: '' }}
                        </div>
                        <div class="text-center" id="preview_shop_phone" style="min-height: 15px;">
                            {{ $shopPhone ?: '' }}
                        </div>
                        <div class="text-center" id="preview_shop_email" style="min-height: 15px;">
                            {{ $shopEmail ?: '' }}
                        </div>
                        <div class="text-center border-top pt-2 mt-2" id="preview_footer_message" style="min-height: 20px; font-size: 0.75rem;">
                            {{ $footerMessage ?: 'Thank you for your business!' }}
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        This is how your shop information will appear on invoices
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Logo preview
    function previewLogo(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('logo_preview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeLogo() {
        if (confirm('Are you sure you want to remove the logo?')) {
            // Add hidden input to indicate logo removal
            const form = document.querySelector('form');
            const removeInput = document.createElement('input');
            removeInput.type = 'hidden';
            removeInput.name = 'remove_logo';
            removeInput.value = '1';
            form.appendChild(removeInput);
            
            // Hide preview
            document.getElementById('logo_preview').style.display = 'none';
            document.getElementById('shop_logo').value = '';
            
            // Submit form
            form.submit();
        }
    }

    // Live preview update
    document.getElementById('shop_name').addEventListener('input', function() {
        document.getElementById('preview_shop_name').textContent = this.value || 'POS SYSTEM';
    });

    document.getElementById('shop_address').addEventListener('input', function() {
        document.getElementById('preview_shop_address').textContent = this.value || '';
    });

    document.getElementById('shop_phone').addEventListener('input', function() {
        document.getElementById('preview_shop_phone').textContent = this.value || '';
    });

    document.getElementById('shop_email').addEventListener('input', function() {
        document.getElementById('preview_shop_email').textContent = this.value || '';
    });

    document.getElementById('footer_message').addEventListener('input', function() {
        document.getElementById('preview_footer_message').textContent = this.value || 'Thank you for your business!';
    });
</script>
@endsection

