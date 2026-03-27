<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreAddressRequest;
use App\Http\Requests\Customer\UpdateAddressRequest;
use App\Http\Resources\V1\CustomerAddressResource;
use App\Models\CustomerAddress;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AddressController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $addresses = CustomerAddress::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();

        return CustomerAddressResource::collection($addresses);
    }

    public function store(StoreAddressRequest $request): CustomerAddressResource
    {
        $user = $request->user();

        if ($request->boolean('is_default')) {
            CustomerAddress::query()->where('user_id', $user->id)->update(['is_default' => false]);
        }

        $address = CustomerAddress::create(array_merge(
            $request->validated(),
            ['user_id' => $user->id]
        ));

        return new CustomerAddressResource($address);
    }

    public function show(Request $request, CustomerAddress $address): CustomerAddressResource
    {
        $this->authorizeAddress($request, $address);

        return new CustomerAddressResource($address);
    }

    public function update(UpdateAddressRequest $request, CustomerAddress $address): CustomerAddressResource
    {
        $this->authorizeAddress($request, $address);

        if ($request->boolean('is_default')) {
            CustomerAddress::query()
                ->where('user_id', $request->user()->id)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($request->validated());

        return new CustomerAddressResource($address->fresh());
    }

    public function destroy(Request $request, CustomerAddress $address): Response
    {
        $this->authorizeAddress($request, $address);
        $address->delete();

        return response()->noContent();
    }

    private function authorizeAddress(Request $request, CustomerAddress $address): void
    {
        if ((int) $address->user_id !== (int) $request->user()->id) {
            abort(403);
        }
    }
}
