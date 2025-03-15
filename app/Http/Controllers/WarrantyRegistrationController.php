<?php

namespace App\Http\Controllers;

use App\Models\WarrantyRegistration;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use App\Utility\EmailUtility;
use Illuminate\Support\Facades\Auth;
use DB;

class WarrantyRegistrationController extends Controller
{
    // Display the warranty registration history
    public function warranty_registration_index()
    {
        $warranty_registration = WarrantyRegistration::with('product')
        ->where('user_id', Auth::id())
        ->paginate(10);
        $products = Product::all();
        return view('frontend.user.warranty_registration.index', compact('warranty_registration', 'products'));
    }

    public function warranty_registration_index_admin(Request $request)
    {
        $query = null;

        $warranties = WarrantyRegistration::query();

        // Search by product name, serial number, or SKU
        if ($request->search != null) {
            $sort_search = $request->search;
            $warranties->where(function ($query) use ($sort_search) {
                $query->whereHas('product', function ($q) use ($sort_search) {
                    $q->where('name', 'like', '%' . $sort_search . '%');
                })->orWhere('serial_no', 'like', '%' . $sort_search . '%');


                // Also filter by user name if provided
                $query->orWhereHas('user', function ($q) use ($sort_search) {
                    $q->where('name', 'like', '%' . $sort_search . '%'); // Assuming 'name' is the column in users table
                });
            });
        }

        // Search by date range
        if ($request->date_from != null && $request->date_to != null) {
            $warranties->whereBetween('date_of_purchase', [$request->date_from, $request->date_to]);
        } elseif ($request->date_from != null) {
            $warranties->whereDate('date_of_purchase', '>=', $request->date_from);
        } elseif ($request->date_to != null) {
            $warranties->whereDate('date_of_purchase', '<=', $request->date_to);
        }

        // Default sorting by newest first
        $warranties = $warranties->orderBy('created_at', 'desc')->paginate(15);

        return view('backend.warranty_registration.index', compact('warranties', 'query'));
    }

    public function warranty_registration_store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'serial_no' => 'required|string|unique:warranty_registrations',
            'date_of_purchase' => 'required|date',
            'bill_image' => 'required|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        // Handle validation failure
        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            return response()->json([
                'status' => 'error',
                'message' => $errors
            ], 200);
        }

        // Ensure the directory exists
        $billImagePath = public_path('uploads/bill_img');
        if (!File::exists($billImagePath)) {
            File::makeDirectory($billImagePath, 0777, true);
        }

        // Replace spaces with hyphens in the file name
        $bill_image_name = time() . '_' . str_replace(' ', '-', $request->file('bill_image')->getClientOriginalName());

        // Move the uploaded file to public/uploads/bill_img
        $request->file('bill_image')->move($billImagePath, $bill_image_name);

        WarrantyRegistration::create([
            'product_id' => $request->product_id,
            'user_id' => auth()->user()->id,
            'serial_no' => $request->serial_no,
            'date_of_purchase' => $request->date_of_purchase,
            'bill_image' => 'uploads/bill_img/' . $bill_image_name,
            'status' => 0, // Pending approval
        ]);

        
        return response()->json([
            'status' => 'success',
            'message' => 'Warranty registration submitted!',
        ], 200);
    }

    // Cancel (Delete) warranty registration
    public function warranty_registration_cancel($id)
    {
        $warranty = WarrantyRegistration::findOrFail($id);
        $warranty->delete();

        return redirect()->route('warranty_registration_history.index')
                         ->with('success', 'Warranty registration has been canceled.');
    }

    public function warranty_registration_cancel_admin($id)
    {
        $warranty = WarrantyRegistration::findOrFail($id);
        $warranty->delete();

        flash(translate('Warranty registration has been canceled'))->success();

        return redirect()->route('warranty_registration_admin.index');
    }


    public function edit($id)
    {
        $registration = WarrantyRegistration::findOrFail($id);
        return response()->json($registration);
    }


    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'serial_no' => 'required|string|unique:warranty_registrations,serial_no,' . $id,
            'date_of_purchase' => 'required|date',
            'bill_image' => 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        // Handle validation failure
        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            return response()->json([
                'status' => 'error',
                'message' => $errors
            ], 200);
        }

        $registration = WarrantyRegistration::findOrFail($id);

        // Update fields
        $registration->product_id = $request->product_id;
        $registration->user_id = auth()->user()->id;
        $registration->serial_no = $request->serial_no;
        $registration->date_of_purchase = $request->date_of_purchase;

        // Handle image upload (if provided)
        if ($request->hasFile('bill_image')) {
            // Ensure the upload folder exists
            $uploadPath = public_path('uploads/bill_img');
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0777, true);
            }
        
            // Delete old image if it exists
            if ($registration->bill_image && File::exists($uploadPath . '/' . $registration->bill_image)) {
                File::delete($uploadPath . '/' . $registration->bill_image);
            }
        
            // Replace spaces with hyphens in the file name
            $imageName = time() . '_' . str_replace(' ', '-', $request->file('bill_image')->getClientOriginalName());
        
            // Store new image
            $request->file('bill_image')->move($uploadPath, $imageName);
            $registration->bill_image = $imageName;
        }

        $registration->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Warranty Registration updated successfully!',
        ], 200);
    }


    public function approval(Request $request)
    {
        $registration = WarrantyRegistration::findOrFail($request->id);

        $status = ($request->approval_status == 'approve') ? 1 : 0;

        $registration->status = $status;
        $registration->note = $request->note;

        $registration->save();

        try {
            if ($status == 1) {
                EmailUtility::warranty_approval_email($registration);
                flash(translate('Warranty Approved Successfully'))->success();
            } else {
                EmailUtility::warranty_reject_email($registration);
                flash(translate('Warranty Rejected Successfully'))->success();
            }
        } catch (\Exception $e) {
            flash(translate('Email sending failed'))->error();
        }

        return back();
    }
}