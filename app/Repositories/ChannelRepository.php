<?php

namespace App\Repositories;

use App\Models\Channel;
use Illuminate\Database\Eloquent\Model;

class ChannelRepository extends AbstractCRUDRepository
{
    public static function getTokenId($token): ?int
    {
        if (!$token) {
            return null;
        }
        return Channel::where('access_token', 'like', '%' . $token)->first()->token_id ?? null;
    }

    public static function getTokenName($token): ?string
    {
        return Channel::where('access_token', 'like', '%' . $token)->first()->name ?? null;
    }

    public function model(): string
    {
        return Channel::class;
    }

    public function create(array $data): Model
    {
        $token = auth()->user()->createToken(Request()->get('name'));

        $data['token_id'] = $token->accessToken->id;
        $data['access_token'] = $token->plainTextToken;

        return parent::create($data);
    }

    public function update(array $data): Model
    {
        unset($data['token_id']);
        unset($data['access_token']);

        return parent::update($data);
    }

    public function refreshToken(int $id): Model
    {
        $channel = Channel::where('id', $id)->first();

        if(!$channel) throw new \Exception('Channel not found');

        $name = Request()->get('name');
        $token = auth()->user()->createToken($channel->name);

        $channel->access_token = $token->plainTextToken;
        $channel->save();
        return $channel;
    }
}
