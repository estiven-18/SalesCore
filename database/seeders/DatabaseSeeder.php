<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\customer;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = collect([
            ['name' => 'Administrador', 'active' => true],
            ['name' => 'Vendedor', 'active' => true],
            ['name' => 'Bodega', 'active' => true],
        ])->map(fn (array $role) => Role::firstOrCreate(['name' => $role['name']], $role));

        $admin = User::firstOrCreate(
            ['email' => '1@gmail.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('1'),
                'active' => true,
            ]
        );

        $seller = User::firstOrCreate(
            ['email' => 'vendedor@salescore.test'],
            [
                'name' => 'Vendedor Demo',
                'password' => Hash::make('password'),
                'active' => true,
            ]
        );

        $adminRole = $roles->firstWhere('name', 'Administrador');
        $sellerRole = $roles->firstWhere('name', 'Vendedor');

        if ($adminRole) {
            $admin->roles()->syncWithoutDetaching([$adminRole->id]);
        }

        if ($sellerRole) {
            $seller->roles()->syncWithoutDetaching([$sellerRole->id]);
        }

        collect([
            [
                'name' => 'Cliente General',
                'document' => '900000001',
                'phone' => '3000000001',
                'email' => 'cliente.general@salescore.test',
                'address' => 'Direccion principal',
                'active' => true,
            ],
            [
                'name' => 'Cliente Frecuente',
                'document' => '900000002',
                'phone' => '3000000002',
                'email' => 'cliente.frecuente@salescore.test',
                'address' => 'Sucursal norte',
                'active' => true,
            ],
        ])->each(fn (array $data) => customer::firstOrCreate(['email' => $data['email']], $data));

        $categories = collect([
            ['name' => 'Bebidas', 'active' => true],
            ['name' => 'Snacks', 'active' => true],
            ['name' => 'Aseo', 'active' => true],
        ])->map(fn (array $category) => Category::firstOrCreate(['name' => $category['name']], $category));

        $products = collect([
            [
                'name' => 'Agua 600ml',
                'description' => 'Botella de agua sin gas',
                'price' => 1500,
                'stock' => 120,
                'tax_rate' => 0,
                'active' => true,
                'categories' => ['Bebidas'],
            ],
            [
                'name' => 'Gaseosa Cola 1.5L',
                'description' => 'Bebida carbonatada sabor cola',
                'price' => 5500,
                'stock' => 80,
                'tax_rate' => 19,
                'active' => true,
                'categories' => ['Bebidas'],
            ],
            [
                'name' => 'Papas Clasicas 45g',
                'description' => 'Snack de papa crocante',
                'price' => 2300,
                'stock' => 150,
                'tax_rate' => 19,
                'active' => true,
                'categories' => ['Snacks'],
            ],
            [
                'name' => 'Jabon Liquido 500ml',
                'description' => 'Jabon liquido antibacterial',
                'price' => 8900,
                'stock' => 40,
                'tax_rate' => 5,
                'active' => true,
                'categories' => ['Aseo'],
            ],
        ]);

        $products->each(function (array $data) use ($categories): void {
            $categoryNames = $data['categories'];
            unset($data['categories']);

            $product = Product::updateOrCreate(['name' => $data['name']], $data);

            $categoryIds = $categories
                ->whereIn('name', $categoryNames)
                ->pluck('id')
                ->all();

            if ($categoryIds !== []) {
                $product->categories()->syncWithoutDetaching($categoryIds);
            }
        });
    }
}
