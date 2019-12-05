class {{ $entity_name }} extends entity
{
    /* generated code start */
    public $structs = [
@foreach ($relationship_infos['relationships'] as $attritube_name => $relationship)
@if ($relationship['relationship_type'] === 'belongs_to')
        '{{ $attritube_name }}_id' => '',
@foreach ($relationship['snaps'] as $structs)
@foreach ($structs as $struct_name => $struct)
@if (array_key_exists('default', $struct['database_field']))
@php
$struct_default = $struct['database_field']['default'];
@endphp
@if (is_string($struct_default))
        '{{ $struct_name }}' => '{{ $struct_default }}',
@elseif (is_null($struct_default))
        '{{ $struct_name }}' => '',
@else
        '{{ $struct_name }}' => {{ $struct_default }},
@endif
@else
        '{{ $struct_name }}' => '',
@endif
@endforeach
@endforeach
@endif
@endforeach
@foreach ($entity_info['structs'] as $struct_name => $struct)
@if (array_key_exists('default', $struct['database_field']))
@php
$struct_default = $struct['database_field']['default'];
@endphp
@if (is_string($struct_default))
        '{{ $struct_name }}' => '{{ $struct_default }}',
@elseif (is_null($struct_default))
        '{{ $struct_name }}' => '',
@else
        '{{ $struct_name }}' => {{ $struct_default }},
@endif
@else
        '{{ $struct_name }}' => '',
@endif
@endforeach
    ];

    public static $entity_display_name = '{{ $entity_info['display_name'] }}';
    public static $entity_description = '{{ $entity_info['description'] }}';

    public static $struct_types = [
@foreach ($relationship_infos['relationships'] as $attritube_name => $relationship)
@if ($relationship['relationship_type'] === 'belongs_to')
        '{{ $attritube_name }}_id' => 'number',
@foreach ($relationship['snaps'] as $structs)
@foreach ($structs as $struct_name => $struct)
        '{{ $struct_name }}' => '{{ $struct['data_type'] }}',
@endforeach
@endforeach
@endif
@endforeach
@foreach ($entity_info['structs'] as $struct_name => $struct)
        '{{ $struct_name }}' => '{{ $struct['data_type'] }}',
@endforeach
    ];

    public static $struct_display_names = [
@foreach ($relationship_infos['relationships'] as $attritube_name => $relationship)
@if ($relationship['relationship_type'] === 'belongs_to')
        '{{ $attritube_name }}_id' => '{{ $relationship['entity_display_name'] }}ID',
@foreach ($relationship['snaps'] as $structs)
@foreach ($structs as $struct_name => $struct)
        '{{ $struct_name }}' => '{{ $struct['display_name'] }}',
@endforeach
@endforeach
@endif
@endforeach
@foreach ($entity_info['structs'] as $struct_name => $struct)
        '{{ $struct_name }}' => '{{ $struct['display_name'] }}',
@endforeach
    ];

    public static $struct_descriptions = [
@foreach ($relationship_infos['relationships'] as $attritube_name => $relationship)
@if ($relationship['relationship_type'] === 'belongs_to')
        '{{ $attritube_name }}_id' => '{{ $relationship['entity_display_name'] }}ID',
@foreach ($relationship['snaps'] as $structs)
@foreach ($structs as $struct_name => $struct)
        '{{ $struct_name }}' => '{{ $struct['description'] }}',
@endforeach
@endforeach
@endif
@endforeach
@foreach ($entity_info['structs'] as $struct_name => $struct)
        '{{ $struct_name }}' => '{{ $struct['description'] }}',
@endforeach
    ];
@foreach ($entity_info['structs'] as $struct_name => $struct)
@if ($struct['data_type'] === 'enum')

@foreach ($struct['formater'] as $value => $description)
    const {{ strtoupper($struct_name.'_'.$value) }} = '{{ strtoupper($struct_name) }}';
@endforeach

    const {{ strtoupper($struct_name) }}_MAPS = [
@foreach ($struct['formater'] as $value => $description)
        self::{{ strtoupper($struct_name.'_'.$value) }} => '{{ $description }}',
@endforeach
    ];
@endif
@endforeach

    public function __construct()
    {/*^{^{^{*/
@foreach ($relationship_infos['relationships'] as $attritube_name => $relationship)
@php
$entity = $relationship['entity'];
$relationship_type = $relationship['relationship_type'];
@endphp
@if ($attritube_name === $entity)
        $this->{{ $relationship_type }}('{{ $attritube_name }}');
@else
@if ($relationship_type === 'belongs_to')
        $this->{{ $relationship_type }}('{{ $attritube_name }}', '{{ $entity }}', '{{ $attritube_name }}_id');
@else
        $this->{{ $relationship_type }}('{{ $attritube_name }}', '{{ $entity }}');
@endif
@endif
@endforeach
    }/*}}}*/

@php
$param_infos = [];
$setting_lines = [];
foreach ($relationship_infos['relationships'] as $attritube_name => $relationship) {
    $entity = $relationship['entity'];
    if ($relationship['relationship_type'] === 'belongs_to' && $relationship['association_type'] === 'composition') {
        $param_infos[] = "$entity $$attritube_name";
        $setting_lines[] = "$$entity_name->$attritube_name = $$attritube_name";
    }
}
foreach ($entity_info['structs'] as $struct_name => $struct) {
    if ($struct['require']) {
        $param_infos[] = "$$struct_name";
        $setting_lines[] = "$$entity_name->$struct_name = $$struct_name";
    }
}
@endphp
    public static function create({{ implode(', ', $param_infos) }})
    {/*^{^{^{*/
@if (empty($param_infos))
        return parent::init();
@else
        ${{ $entity_name }} = parent::init();

@foreach ($setting_lines as $setting_line)
        {{ $setting_line }};
@endforeach

        return ${{ $entity_name }};
@endif
    }/*}}}*/

    protected function struct_formaters($property)
    {/*^{^{^{*/
        $formaters = [
@foreach ($entity_info['structs'] as $struct_name => $struct)
@if (isset($struct['formater']))
@if ($struct['data_type'] === 'enum')
            '{{ $struct_name }}' => self::{{ strtoupper($struct_name) }}_MAPS,
@else
            '{{ $struct_name }}' => [
@foreach ($struct['formater'] as $formater)
                [
@if (isset($formater['reg']))
                    'reg' => '{{ $formater['reg'] }}',
                    'failed_message' => '{{ $formater['failed_message'] }}',
@elseif (isset($formater['function']))
                    'function' => function ($value) {
                        return {{ $formater['function'] }};
                    },
                    'failed_message' => '{{ $formater['failed_message'] }}',
@endif
                ],
@endforeach
            ],
@endif
@endif
@endforeach
        ];

        return $formaters($property) ?? false;
    }/*}}}*/
@foreach ($entity_info['structs'] as $struct_name => $struct)
@if ($struct['data_type'] === 'enum')

    public function get_{{ $struct_name }}_description()
    {/*^{^{^{*/
        return self::{{ strtoupper($struct_name) }}_MAPS[$this->{{ $struct_name }}];
    }/*}}}*/
@foreach ($struct['formater'] as $value => $description)

    public function {{ $struct_name }}_is_{{ strtolower($value) }}()
    {/*^{^{^{*/
        return $this->{{ $struct_name }} === self::{{ strtoupper($struct_name.'_'.$value) }};
    }/*}}}*/

    public function set_{{ $struct_name }}_{{ strtolower($value) }}()
    {/*^{^{^{*/
        return $this->{{ $struct_name }} = self::{{ strtoupper($struct_name.'_'.$value) }};
    }/*}}}*/
@endforeach
@endif
@endforeach
@foreach ($relationship_infos['relationships'] as $attritube_name => $relationship)
@php
$entity = $relationship['entity'];
@endphp
@if ($relationship['relationship_type'] === 'belongs_to')

    public function belongs_to_{{ $attritube_name }}({{ $entity }} ${{ $entity }})
    {/*^{^{^{*/
        return $this->{{ $attritube_name }}_id == ${{ $entity }}->id;
    }/*}}}*/
@endif
@endforeach
    /* generated code end */
}