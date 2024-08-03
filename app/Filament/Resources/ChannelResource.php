<?php 

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use App\Filament\Resources\ChannelResource\Pages;
use App\Models\Channel;
use Illuminate\Support\HtmlString;

class ChannelResource extends Resource
{
    protected static ?string $model = Channel::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    public static function getNavigationBadge(): ?string
    {
        return new HtmlString('🟢 0 Issues');


    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('base_url')
                    ->required()
                    ->url(),
                Forms\Components\TextInput::make('consumer_key')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('consumer_secret')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('base_url')->sortable()->searchable(),
            ])
            ->filters([
                // Add any filters you need here
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChannels::route('/'),
            'create' => Pages\CreateChannel::route('/create'),
            'edit' => Pages\EditChannel::route('/{record}/edit'),
        ];
    }
}

