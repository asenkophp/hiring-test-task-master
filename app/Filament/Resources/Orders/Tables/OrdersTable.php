<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Jobs\ProcessOrderPaymentJob;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('id')
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => Order::STATUS_PENDING,
                        'success' => Order::STATUS_PAID,
                        'primary' => Order::STATUS_SHIPPED,
                        'danger'  => Order::STATUS_CANCELLED,
                    ]),

                TextColumn::make('display_total')
                    ->label('Total'),

                TextColumn::make('logs_count')
                    ->counts('logs')
                    ->label('Events'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),

            ])
            ->filters([

                SelectFilter::make('status')
                    ->options([
                        Order::STATUS_PENDING   => 'В ожидании',
                        Order::STATUS_PAID      => 'Оплачен',
                        Order::STATUS_SHIPPED   => 'Доставлен',
                        Order::STATUS_CANCELLED => 'Отменен',
                    ]),

            ])
            ->recordActions([

                Action::make('processPayment')
                    ->label('Обработать платеж')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->visible(fn (Order $record) => $record->status === Order::STATUS_PENDING)
                    ->requiresConfirmation()
                    ->action(function (Order $record): void {

                        ProcessOrderPaymentJob::dispatch($record->id);

                        Notification::make()
                            ->title('Задание платежа поставлено в очередь')
                            ->success()
                            ->send();
                    }),

                EditAction::make(),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(100)
            ->paginationPageOptions([100])
            ->defaultSort('created_at', 'desc');
    }
}
