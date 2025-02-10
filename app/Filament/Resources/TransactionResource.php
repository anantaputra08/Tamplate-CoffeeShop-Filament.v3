<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $navigationIcon = 'heroicon-c-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_id')
                    ->label('Order ID')
                    // ->default(fn() => rand(100000, 999999)) // Generate otomatis
                    ->default(fn() => Transaction::generateOrderId())
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->label('Cashier')
                    ->default(fn() => Auth::id()) // Ambil user yang login
                    ->relationship('user', 'name')
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->default('pending')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->dehydrated()
                    ->required(),
                Forms\Components\Repeater::make('items')
                    ->relationship('items')
                    ->addActionLabel('Add Orders')
                    ->reorderable(true)
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->cloneable()
                    ->columnSpanFull()
                    ->grid(2)
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, callable $set, callable $get) => self::updateItemPrice($set, $get)),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(0)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, callable $set, callable $get) => self::updateItemPrice($set, $get)),
                            ]),
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Radio::make('sugar_level')
                                    ->label('Sugar Level')
                                    ->options([
                                        'no_sugar' => 'No Sugar',
                                        'less_sugar' => 'Less Sugar',
                                        'normal_sugar' => 'Normal Sugar',
                                    ])
                                    ->columns(3)
                                    ->dehydrated(),
                                Forms\Components\Radio::make('iced_level')
                                    ->label('Iced Level')
                                    ->options([
                                        'no_ice' => 'No Ice',
                                        'less_ice' => 'Less Ice',
                                        'normal_ice' => 'Normal Ice',
                                    ])
                                    ->columns(3)
                                    ->dehydrated(),
                                Forms\Components\Toggle::make('take_away')
                                    ->label('Take Away')
                                    ->columns(3)
                                    ->dehydrated(),
                            ]),
                        Forms\Components\TextInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->prefix('Rp.')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->afterStateUpdated(
                                fn($state, callable $set, callable $get) =>
                                self::updateItemPrice($set, $get) // Tambahkan di sini
                            ),
                    ])
                    ->afterStateUpdated(
                        fn($state, callable $set, callable $get) =>
                        self::updateTotalPrice($set, $get)
                    )
                    ->afterStateHydrated(
                        fn(callable $set, callable $get) =>
                        self::mergeDuplicateItems($set, $get)
                    ),

                Forms\Components\Select::make('payment_type')
                    ->label('Payment Type')
                    ->options([
                        'cash' => 'Cash',
                        'credit_card' => 'Credit Card',
                        'bank_transfer' => 'Bank Transfer',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('gross_amount')
                    ->label('Gross Amount')
                    ->numeric()
                    ->prefix('Rp.')
                    ->disabled()
                    ->dehydrated()
                    ->reactive(),
            ]);
    }
    private function generateOrderId()
    {
        $today = now()->format('Ymd');
        $countToday = DB::table('orders')
            ->whereDate('created_at', now()->toDateString())
            ->count() + 1;

        return $today . str_pad($countToday, 5, '0', STR_PAD_LEFT);
    }
    public static function updateItemPrice(callable $set, callable $get)
    {
        $productPrice = \App\Models\Product::find($get('product_id'))?->price ?? 0;
        $quantity = $get('quantity') ?? 1;

        $totalItemPrice = $productPrice * $quantity;
        $set('price', $totalItemPrice);

        // Panggil updateTotalPrice setelah harga item diubah
        self::updateTotalPrice($set, $get);
    }

    public static function updateTotalPrice(callable $set, callable $get)
    {
        // Pastikan setiap item memiliki harga yang terbaru sebelum menjumlahkan
        $items = collect($get('items') ?? [])->map(function ($item) {
            $productPrice = \App\Models\Product::find($item['product_id'])?->price ?? 0;
            $quantity = $item['quantity'] ?? 1;

            return $productPrice * $quantity;
        });

        // Set total harga dengan hasil penjumlahan terbaru
        $set('gross_amount', $items->sum());
    }

    public static function mergeDuplicateItems(callable $set, callable $get)
    {
        $items = collect($get('items') ?? []);
        $mergedItems = collect();

        $items->each(function ($item) use ($mergedItems) {
            $existingItemKey = $mergedItems->search(function ($existingItem) use ($item) {
                return $existingItem['product_id'] === $item['product_id'] &&
                    $existingItem['sugar_level'] === $item['sugar_level'] &&
                    $existingItem['iced_level'] === $item['iced_level'] &&
                    $existingItem['take_away'] === $item['take_away'];
            });

            if ($existingItemKey !== false) {
                $mergedItems->transform(function ($existingItem, $key) use ($item, $existingItemKey) {
                    if ($key === $existingItemKey) {
                        $existingItem['quantity'] += $item['quantity'];
                    }
                    return $existingItem;
                });
            } else {
                $mergedItems->push($item);
            }
        });

        $set('items', $mergedItems->toArray());

        // Update item prices after merging
        $mergedItems->each(function ($item, $index) use ($set, $get) {
            $productPrice = \App\Models\Product::find($item['product_id'])?->price ?? 0;
            $quantity = $item['quantity'] ?? 1;
            $totalItemPrice = $productPrice * $quantity;
            $set("items.$index.price", $totalItemPrice);
        });

        self::updateTotalPrice($set, $get);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cashier')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gross_amount')
                    ->label('Gross Amount')
                    ->money('Rp.')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->sortable()
                    ->searchable()
                    ->color(fn($record) => match ($record->status) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('payment_type')
                    ->searchable()
                    ->color(fn($record) => match ($record->payment_type) {
                        'cash' => 'success',
                        'credit_card' => 'primary',
                        'bank_transfer' => 'info',
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('klickToPayment')
                    ->label('Klick to Payment')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->color('info')
                    ->visible(fn($record) => $record->status === 'pending' && $record->payment_type === 'bank_transfer')
                    ->action(function ($record) {
                        $record->update(['status' => 'processing']);

                        Notification::make()
                            ->title('Status Updated')
                            ->body('The status has been marked as processing.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('markAsProcessing')
                    ->label('Mark as Processing')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->color('info')
                    ->visible(fn($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update(['status' => 'processing']);

                        Notification::make()
                            ->title('Status Updated')
                            ->body('The status has been marked as processing.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('markAsCompleted')
                    ->label('Mark as Completed')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'processing')
                    ->action(function ($record) {
                        $record->update(['status' => 'completed']);

                        Notification::make()
                            ->title('Status Updated')
                            ->body('The status has been marked as completed.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            // 'view' => Pages\ViewTransaction::route('/{record}/view'),
            'create' => Pages\CreateTransaction::route('/create'),
            // 'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}