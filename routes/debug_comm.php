<?php

use Illuminate\Support\Facades\Route;
use App\Models\SalesOrder;
use App\Models\Contact;
use App\Models\User;

Route::get('/debug-commission-user/{id}', function ($id) {
    $order = SalesOrder::findOrFail($id);
    
    $partnerId = $order->partner_id;
    $contact = Contact::where('odoo_id', $partnerId)->first();
    
    $contactUser = $contact ? $contact->user : null;
    $orderUser = User::find($order->user_id);
    
    return [
        'order_id' => $order->id,
        'order_name' => $order->name,
        'partner_id_raw' => $partnerId,
        'contact_found' => $contact ? 'Yes' : 'No',
        'contact_id' => $contact ? $contact->id : null,
        'contact_odoo_id' => $contact ? $contact->odoo_id : null,
        'contact_assigned_user_id' => $contact ? $contact->user_id : null,
        'contact_assigned_user_name' => $contactUser ? $contactUser->name : null,
        'order_user_id_fallback' => $order->user_id,
        'order_user_fallback_found' => $orderUser ? 'Yes' : 'No',
    ];
});
