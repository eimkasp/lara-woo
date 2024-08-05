<?php 

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'email')
                    ->required(),
                Forms\Components\TextInput::make('rating')
                    ->required(),
                Forms\Components\Textarea::make('review')
                    ->required(),
                Forms\Components\Checkbox::make('verified')
                    ->label('Verified Review'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->sortable(),
                Tables\Columns\TextColumn::make('customer.email')->sortable(),
                Tables\Columns\TextColumn::make('rating')->sortable(),
                Tables\Columns\TextColumn::make('review')->limit(50),
                Tables\Columns\BooleanColumn::make('verified')->label('Verified'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime(),
            ])
            ->filters([
                // Add any filters you need here
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
            // 'view' => Pages\ViewReview::route('/{record}'),
        ];
    }
}
