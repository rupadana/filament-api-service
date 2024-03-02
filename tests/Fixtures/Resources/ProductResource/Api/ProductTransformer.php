<?php

namespace Rupadana\ApiService\Tests\Fixtures\Resources\ProductResource\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductTransformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $product = $this->resource->toArray();

        return [
            ...$product,
            'hash' => md5($product['name']),
        ];
    }
}
