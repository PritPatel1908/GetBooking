# API Response Examples - After Code Update

## Updated Code Features

✅ **Always reloads booking_details** with relationships  
✅ **Multiple fallback methods** to get ground data  
✅ **Automatic slot loading** if relationship not loaded  
✅ **Enhanced relationship handling** in all methods

---

## 1. Booking List API Response

**Endpoint:** `GET /api/bookings`

**Expected Response Structure:**

```json
{
    "success": true,
    "message": "Bookings retrieved successfully",
    "data": [
        {
            "id": 6,
            "booking_sku": "BK6914B11732CE56.830830086791",
            "user_id": 4,
            "booking_date": "2025-11-17",
            "booking_date_formatted": "17 Nov 2025",
            "booking_time": "10:00 - 11:00",
            "duration": 1,
            "amount": 5,
            "amount_formatted": "₹5.00",
            "booking_status": "pending",
            "payment_status": null,
            "notes": "Offline payment - Payment to be made at ground",
            "created_at": "2025-11-12 16:08:55",
            "created_at_formatted": "12 Nov 2025, 04:08 PM",
            "updated_at": "2025-11-12 16:08:55",
            "updated_at_formatted": "12 Nov 2025, 04:08 PM",

            "user": {
                "id": 4,
                "name": "Prit Patel",
                "email": "prit89039@gmail.com",
                "phone": "9510862562",
                "address": "G-501, Daksh Recidency, Nikol - Naroda Road, 382350",
                "city": "Ahmedabad",
                "state": "Gujrat",
                "postal_code": "382350"
            },

            "ground": {
                "id": 2,
                "name": "Test Ground",
                "location": "Test Location",
                "description": "test",
                "ground_image": null,
                "phone": "1234567890",
                "email": "prit21276@gmail.com",
                "capacity": 10,
                "ground_type": "turf",
                "ground_category": "cricket",
                "opening_time": "00:00",
                "closing_time": "23:59",
                "status": "active",
                "rules": "test",
                "images": [
                    {
                        "id": 1,
                        "ground_id": 2,
                        "image_path": "uploads/grounds/ground_2_68d4be9849b7e_1758772888.png",
                        "image_url": "https://getbooking.in/uploads/grounds/ground_2_68d4be9849b7e_1758772888.png"
                    }
                ],
                "features": [
                    {
                        "id": 9,
                        "ground_id": 2,
                        "feature_name": "test",
                        "feature_type": "facility",
                        "feature_status": "active"
                    }
                ]
            },

            "ground_slot_detail": {
                "id": 13,
                "ground_id": 2,
                "slot_name": "10:00 - 11:00",
                "start_time": "10:00:00.0000000",
                "end_time": "11:00:00.0000000",
                "slot_type": "morning",
                "day_of_week": "sunday",
                "slot_status": "active",
                "price_per_slot": 1,
                "time_range": "10:00:00.0000000 - 11:00:00.0000000"
            },

            "details": [
                {
                    "id": 15,
                    "booking_id": 6,
                    "ground_id": 2,
                    "slot_id": 13,
                    "booking_time": "10:00 - 11:00",
                    "time_slot": "10:00 - 11:00",
                    "duration": 1,
                    "price": 5,
                    "created_at": "2025-11-12 16:08:55",
                    "updated_at": "2025-11-12 16:08:55",
                    "slot": {
                        "id": 13,
                        "ground_id": 2,
                        "slot_name": "10:00 - 11:00",
                        "start_time": "10:00:00.0000000",
                        "end_time": "11:00:00.0000000",
                        "slot_type": "morning",
                        "day_of_week": "sunday",
                        "slot_status": "active",
                        "price_per_slot": 1,
                        "time_range": "10:00:00.0000000 - 11:00:00.0000000"
                    },
                    "ground": {
                        "id": 2,
                        "name": "Test Ground",
                        "location": "Test Location",
                        "description": "test",
                        "ground_image": null,
                        "phone": "1234567890",
                        "email": "prit21276@gmail.com",
                        "capacity": 10,
                        "ground_type": "turf",
                        "ground_category": "cricket",
                        "opening_time": "00:00",
                        "closing_time": "23:59",
                        "status": "active",
                        "rules": "test",
                        "images": [...],
                        "features": [...]
                    }
                }
            ],

            "payment": {
                "id": 5,
                "booking_id": 6,
                "user_id": 4,
                "transaction_id": null,
                "payment_method": "offline",
                "payment_status": "completed",
                "payment_type": "offline",
                "amount": 5,
                "amount_formatted": "₹5.00",
                "date": "2025-11-12",
                "date_formatted": "12 Nov 2025",
                "payment_url": null,
                "payment_response": "{\"status\":\"pending\",\"message\":\"Offline payment - Payment pending\"}",
                "payment_response_code": null,
                "payment_response_message": "Offline payment - Payment pending",
                "created_at": "2025-11-12 16:08:55",
                "created_at_formatted": "12 Nov 2025, 04:08 PM"
            }
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 10,
        "total": 14,
        "last_page": 2,
        "from": 1,
        "to": 10
    }
}
```

---

## 2. Single Booking API Response

**Endpoint:** `GET /api/bookings/{id}`

**Expected Response Structure:**

```json
{
    "success": true,
    "message": "Booking retrieved successfully",
    "data": {
        "id": 6,
        "booking_sku": "BK6914B11732CE56.830830086791",
        "user_id": 4,
        "booking_date": "2025-11-17",
        "booking_date_formatted": "17 Nov 2025",
        "booking_time": "10:00 - 11:00",
        "duration": 1,
        "amount": 5,
        "amount_formatted": "₹5.00",
        "booking_status": "pending",
        "payment_status": null,
        "notes": "Offline payment - Payment to be made at ground",
        "created_at": "2025-11-12 16:08:55",
        "created_at_formatted": "12 Nov 2025, 04:08 PM",
        "updated_at": "2025-11-12 16:08:55",
        "updated_at_formatted": "12 Nov 2025, 04:08 PM",

        "user": { ... },
        "ground": { ... },
        "ground_slot_detail": { ... },
        "details": [ ... ],
        "payment": { ... }
    }
}
```

---

## Key Improvements Made

### 1. **Always Reload Booking Details**

```php
// Always reload booking_details with relationships to ensure data is fresh
if ($booking->id) {
    $bookingDetails = BookingDetail::where('booking_id', $booking->id)
        ->with(['ground.images', 'ground.features', 'slot'])
        ->get();
    if ($bookingDetails->isNotEmpty()) {
        $booking->setRelation('details', $bookingDetails);
    }
}
```

### 2. **Multiple Fallback Methods for Ground**

-   **Method 1:** From booking detail (primary)
-   **Method 2:** From booking slot (if direct slot_id exists)
-   **Method 3:** Direct query from booking_details table (fallback)

### 3. **Automatic Slot Loading**

```php
// Ensure slot is loaded if slot_id exists
if ($firstDetail->slot_id && !$firstDetail->slot) {
    $firstDetail->setRelation('slot', GroundSlot::find($firstDetail->slot_id));
}
```

### 4. **Enhanced getBookingDetails Method**

```php
// Ensure ground is loaded
if (!$detail->ground && $detail->ground_id) {
    $detail->setRelation('ground', Ground::with(['images', 'features'])->find($detail->ground_id));
}

// Ensure slot is loaded
if (!$detail->slot && $detail->slot_id) {
    $detail->setRelation('slot', GroundSlot::find($detail->slot_id));
}
```

---

## What's Fixed

✅ **`ground`** - Ab properly load hoga, chahe booking_details exist karein ya na  
✅ **`ground_slot_detail`** - Ab properly load hoga with slot information  
✅ **`details`** - Ab properly load hoga with all relationships  
✅ **`payment`** - Already working, ab bhi kaam karega

---

## Testing

Aap Flutter app se API call karke check kar sakte hain:

```dart
// Get bookings
final response = await http.get(
  Uri.parse('https://getbooking.in/api/bookings'),
  headers: {
    'Accept': 'application/json',
    'Authorization': 'Bearer YOUR_TOKEN',
  },
);

final data = json.decode(response.body);
if (data['success']) {
  final bookings = data['data'];
  for (var booking in bookings) {
    print('Ground: ${booking['ground']?['name']}'); // Ab null nahi hoga
    print('Slot: ${booking['ground_slot_detail']?['slot_name']}'); // Ab null nahi hoga
  }
}
```

---

**Note:** Agar abhi bhi `ground` ya `ground_slot_detail` null aa raha hai, to check karein:

1. Booking ke paas `booking_details` records hain ya nahi
2. `booking_details` table mein `ground_id` properly set hai ya nahi
3. Database mein actual data hai ya nahi

Code ab properly handle karega sab cases ko! 🚀

