<?php

namespace Waynelogic\FilamentCms\Filament\Settings\Resources\BackendUsers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BackendUsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament-cms::admin.form.first_name'))
                    ->searchable(),
                TextColumn::make('last_name')
                    ->label(__('filament-cms::admin.form.last_name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('filament-cms::admin.form.email'))
                    ->searchable(),
                IconColumn::make('is_super_admin')
                    ->label(__('filament-cms::admin.form.is_super_admin'))
                    ->boolean(),
                TextColumn::make('email_verified_at')
                    ->label(__('filament-cms::admin.form.email_verified_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
