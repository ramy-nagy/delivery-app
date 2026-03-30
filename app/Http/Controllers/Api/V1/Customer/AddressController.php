<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreAddressRequest;
use App\Http\Requests\Customer\UpdateAddressRequest;
use App\Http\Resources\V1\CustomerAddressResource;
use App\Models\CustomerAddress;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AddressController extends Controller
{
    use ApiResponse;
    public function index(Request $request)
    {
        $addresses = CustomerAddress::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();

        return $this->success(CustomerAddressResource::collection($addresses), 'Addresses fetched successfully.');
    }

    public function store(StoreAddressRequest $request)
    {
        $user = $request->user();

        if ($request->boolean('is_default')) {
            CustomerAddress::query()->where('user_id', $user->id)->update(['is_default' => false]);
        }

        $address = CustomerAddress::create(array_merge(
            $request->validated(),
            ['user_id' => $user->id]
        ));

        return $this->success(new CustomerAddressResource($address), 'Address created successfully.');
    }

    public function show(Request $request, CustomerAddress $address)
    {
        $this->authorizeAddress($request, $address);
        return $this->success(new CustomerAddressResource($address), 'Address fetched successfully.');
    }

    public function update(UpdateAddressRequest $request, CustomerAddress $address)
    {
        $this->authorizeAddress($request, $address);

        if ($request->boolean('is_default')) {
            CustomerAddress::query()
                ->where('user_id', $request->user()->id)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($request->validated());

        return $this->success(new CustomerAddressResource($address->fresh()), 'Address updated successfully.');
    }

    public function destroy(Request $request, CustomerAddress $address)
    {
        $this->authorizeAddress($request, $address);
        $address->delete();
        return $this->success(null, 'Address deleted successfully.');
    }

    private function authorizeAddress(Request $request, CustomerAddress $address): void
    {
        if ((int) $address->user_id !== (int) $request->user()->id) {
            abort(403);
        }
    }
}
