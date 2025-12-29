# Medical Store Features - Simplified

## Overview
Your POS system has been extended with medical/pharmacy features while keeping it simple and focused on core POS functionality. No prescription or doctor management - just medical product tracking.

## Features Added

### 1. Medical Product Fields
Products now support medical/pharmacy specific information:
- **Requires Prescription**: Boolean flag (informational only - no validation)
- **HSN Code**: Harmonized System of Nomenclature code for tax purposes
- **Manufacturer**: Medicine manufacturer name
- **Composition**: Active ingredients/composition
- **Schedule**: Drug schedule (H1, H2, X, etc.)
- **Shelf Life Days**: Product shelf life in days

### 2. Batch/Lot Number Tracking
Track medicine batches with:
- **Batch Number**: Unique batch identifier
- **Manufacturing Date**: When the batch was manufactured
- **Expiry Date**: When the batch expires
- **Quantity**: Total quantity in batch
- **Remaining Quantity**: Available quantity in batch
- **Cost Price**: Cost price for this specific batch

**Benefits:**
- Track expiry dates for medicines
- FIFO (First In First Out) - oldest batches used first
- Batch recall capability
- Expiry alerts

### 3. Patient Information
Customers can be marked as patients with additional medical information:
- **Gender**: Male/Female/Other
- **Date of Birth**: Auto-calculates age
- **Blood Group**: A+, B+, O+, etc.
- **Allergies**: List of known allergies
- **Medical History**: Patient medical history notes
- **Is Patient**: Boolean flag to mark as patient

## Database Changes

### New Tables:
1. `product_batches` - Batch/lot tracking for products

### Modified Tables:
1. `products` - Added medical fields (requires_prescription, hsn_code, manufacturer, composition, schedule, shelf_life_days)
2. `customers` - Added patient fields (gender, date_of_birth, blood_group, allergies, medical_history, is_patient)
3. `sale_items` - Added batch_id to track which batch was sold

## Migration Instructions

Run the following command to apply all database changes:

```bash
php artisan migrate
```

This will:
- Add medical fields to products table
- Add patient fields to customers table
- Create product_batches table
- Add batch_id to sale_items table

## Usage Guide

### Setting Up Medical Products

1. **Add Medical Fields to Products**:
   - When creating/editing a product, you can now specify:
     - Whether it requires a prescription (informational)
     - HSN code for tax purposes
     - Manufacturer information
     - Composition details
     - Drug schedule
     - Shelf life in days

2. **Creating Batches** (During Purchase):
   - When making a purchase, create batches with:
     - Batch number
     - Manufacturing date
     - Expiry date
     - Quantity
     - Cost price
   - System automatically tracks remaining quantity

### Using Batches in Sales

1. When selling products with batches:
   - System shows available batches
   - Automatically selects oldest batch first (FIFO)
   - Tracks which batch was sold
   - Updates batch remaining quantity

### Patient Management

1. When creating/editing customers:
   - Mark as "Patient" if applicable
   - Add medical information:
     - Gender, Date of Birth (auto-calculates age)
     - Blood Group
     - Allergies
     - Medical History

## Key Features

### Batch Tracking
- **FIFO System**: Oldest batches (by expiry date) are used first
- **Expiry Tracking**: Track when batches expire
- **Stock Management**: Batch-level stock tracking
- **Traceability**: Know which batch was sold to which customer

### Medical Product Information
- **HSN Codes**: For tax compliance
- **Manufacturer Info**: Track medicine manufacturers
- **Composition**: Know active ingredients
- **Schedule**: Track controlled substances

### Patient Records
- **Complete Medical Profile**: Gender, age, blood group
- **Allergy Tracking**: Important for patient safety
- **Medical History**: Keep patient records

## What Was Removed

- ❌ Doctor Management (not needed)
- ❌ Prescription Management (not needed)
- ❌ Prescription Validation (no enforcement)
- ❌ Prescription-Sale Linking (removed)

## What Remains

- ✅ Medical product fields (informational)
- ✅ Batch/lot tracking with expiry dates
- ✅ Patient information in customers
- ✅ FIFO batch selection
- ✅ Expiry date tracking

## Next Steps

1. **Run Migrations**: `php artisan migrate`
2. **Update Product Forms**: Add medical fields to product create/edit forms
3. **Update Customer Forms**: Add patient fields to customer forms
4. **Update Purchase Form**: Add batch creation during purchase
5. **Update POS**: Show batch selection when selling products with batches
6. **Add Expiry Alerts**: Dashboard alerts for expiring batches (optional)

## Model Relationships

- `Product` has many `ProductBatch`
- `ProductBatch` belongs to `Product` and `Purchase`
- `SaleItem` belongs to `ProductBatch`
- `Customer` can be a patient with medical information

## Benefits for Medical Stores

1. **Compliance**: Track HSN codes, schedules, manufacturers
2. **Safety**: Expiry date tracking prevents selling expired medicines
3. **Traceability**: Batch tracking for recalls
4. **Patient Records**: Keep medical information for customers
5. **Inventory Management**: Batch-level stock control
6. **FIFO**: Automatic oldest-first batch selection


