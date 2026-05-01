<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Основное')
                    ->columns(3)
                    ->schema([

                        Select::make('customer_id')
                            ->label('Заказчик')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('status')
                            ->label('Статус')
                            ->options([
                                Order::STATUS_PENDING   => 'В ожидании',
                                Order::STATUS_PAID      => 'Оплачен',
                                Order::STATUS_SHIPPED   => 'Доставлен',
                                Order::STATUS_CANCELLED => 'Отменен',
                            ])
                            ->default(
                                Order::STATUS_PENDING
                            )
                            ->required(),

                        TextInput::make('total')
                            ->label('Сумма')
                            ->numeric()
                            ->default(1000)
                            ->prefix('$')
                            ->required(),
                    ])
                    ->columnSpanFull(),

                TextInput::make('external_reference')
                    ->label('Внешний ID платежа')
                    ->visibleOn('edit')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),

            ]);
    }
}
