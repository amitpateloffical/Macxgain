<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class CustomersImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $initialCustomerCount = Customer::count();
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue;
            }
            $customerCount = $initialCustomerCount + $index;
            Customer::create([
                'name' => $row[1] ?? null,
                'contactperson' => $row[2] ?? null,
                'email' => $row[3] ?? null,
                'contactnumber' => $row[4] ?? null,
                'assignedto' => $row[5] ?? null,
                'engagement' => $row[6] ?? null,
                'status' => 'I', // Default status
                'customer_id' => '#CI' . $customerCount,
            ]);
        }
    }
}
