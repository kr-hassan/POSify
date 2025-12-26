# Where to Add Expiry Date

## Location: Purchase Form

**Expiry dates are added when creating a new purchase.**

### Steps:

1. **Navigate to Purchases**
   - Go to **Purchases** in the sidebar
   - Click **"Add Purchase"** button

2. **Fill Purchase Details**
   - Select Supplier
   - Enter Purchase Date
   - Enter Paid Amount (if any)

3. **Add Items with Expiry Dates**
   - For each product, you can now enter:
     - **Batch Number** (optional) - e.g., "BATCH001", "LOT12345"
     - **Manufacturing Date** (optional) - When the batch was manufactured
     - **Expiry Date** (optional) - When the batch expires
   
4. **Save Purchase**
   - Click "Save Purchase"
   - System automatically creates batch records with expiry dates

## Important Notes:

- **Expiry dates are optional** - You can leave them blank for non-medical products
- **Batch tracking** - If you enter batch number or expiry date, a batch record is created
- **FIFO System** - When selling, system automatically uses oldest batches first (by expiry date)
- **Auto-calculation** - If you enter manufacturing date, system suggests expiry date (1 year later by default)

## Example:

When purchasing medicines:
- Product: Paracetamol 500mg
- Quantity: 100
- Cost Price: $5.00
- Batch Number: BATCH20241224
- Manufacturing Date: 2024-01-15
- Expiry Date: 2026-01-15

This creates a batch that will be tracked with expiry date.

## Viewing Expiry Dates:

- Expiry dates are tracked in the `product_batches` table
- You can view batches when looking at product details
- System can alert you about expiring batches (to be implemented in reports)

## For Medical Stores:

Always enter expiry dates for medicines to:
- Track expiration
- Ensure FIFO (First In First Out) selling
- Comply with regulations
- Enable expiry alerts


