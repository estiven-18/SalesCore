## por hacer 

//todo: poner que salga el numero de venta
//TODO:QUE SE DESCUANTE DEL INVENTARIO
//TODO: QUE NE BILLING SALGA EL TOTAL, EL IMPUESTO Y EL DESCUENTO POR SEPARADO PERO EN LA MISMA VISTA

- /*  Wizard::make([
                Step::make('Sale Detail')
                    ->schema([
                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')->required(),
                                TextInput::make('email')->required()->email(),
                                TextInput::make('document')->required(),
                                TextInput::make('phone')->required(),
                                TextInput::make('address'),
                            ]),
                        Select::make('user_id')
                            ->relationship(name: 'user', titleAttribute: 'name')
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Step::make('Sale Items')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $product = Product::find($state);
                                        $set('unit_price', $product?->price ?? 0);
                                        $set('tax_rate', $product?->tax_rate ?? 0);
                                    })
                                    ->required(),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->live(),
                                TextInput::make('discount')
                                    ->label('Descuento (%)')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->live(),
                                TextInput::make('tax_rate')
                                    ->numeric()
                                    ->default(0)
                                    ->required()
                                    ->live(),
                                TextInput::make('unit_price')
                                    ->numeric()
                                    ->required()
                                    ->disabled()
                                    ->live(),
                            ])
                            ->columns(5)
                            ->live()
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                self::updateTotal($set, $get);
                            }),
                    ]),
                Step::make('Billing')
                    ->schema([
                        TextInput::make('total')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->default(0)
                            ->afterStateHydrated(function (callable $set, callable $get) {
                                self::updateTotal($set, $get);
                            }),
                    ]),
            ])
            ->columnSpanFull(), */


## por hacer 
- hacer que si esta desactivado no deje seguri o que salga que esta desactivado (ya sea roles, users, sales, products, etc)

## por hacer 
- hacer que en clinets se puede enviar email desde el boton