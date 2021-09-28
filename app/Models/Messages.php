<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    use HasFactory;

    /**
     *
     * @var string
     */
    protected $table = 'messages';
        
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'message',
        'sender_id',
        'receiver_id',
    ];

    /**
     *
     * @var bool
     */
    public $timestamps = true;

    public function users()
    {
        return $this->belongsTo('App\Models\User','sender_id');
    }
}
