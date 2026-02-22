<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'is_admin_message',
        'attachments',
    ];

    protected $casts = [
        'is_admin_message' => 'boolean',
        'attachments' => 'array',
    ];

    /**
     * Get ticket
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class);
    }

    /**
     * Get user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for admin messages
     */
    public function scopeAdmin($query)
    {
        return $query->where('is_admin_message', true);
    }

    /**
     * Scope for customer messages
     */
    public function scopeCustomer($query)
    {
        return $query->where('is_admin_message', false);
    }

    /**
     * Get formatted message time
     */
    public function getFormattedTimeAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d M Y, H:i');
    }
}
