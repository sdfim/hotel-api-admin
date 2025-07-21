<?php

namespace App\Actions\ConfigAttribute;

use App\Models\Configurations\ConfigAttribute;

class CreateConfigAttribute
{
    public function create(array $data): ConfigAttribute
    {
        $categories = $data['categories'] ?? [];
        unset($data['categories']);

        $attribute = ConfigAttribute::create($data);

        if (! empty($categories)) {
            $attribute->categories()->sync($categories);
        }

        return $attribute;

    }
}
