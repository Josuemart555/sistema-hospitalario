{{-- Temporary flat rendering of Option::tree() until OptionsTree.vue replaces this picker. --}}
@foreach($nodes as $node)
    <div class="form-check" style="margin-left: {{ $depth * 1.25 }}rem">
        <input class="form-check-input" type="checkbox" name="options[]" value="{{ $node['id'] }}" id="option{{ $node['id'] }}" @checked(in_array($node['id'], $selected))>
        <label class="form-check-label" for="option{{ $node['id'] }}">{{ $node['name'] }}</label>
    </div>
    @if(!empty($node['children']))
        @include('admin.options.partials.tree-checkboxes', ['nodes' => $node['children'], 'selected' => $selected, 'depth' => $depth + 1])
    @endif
@endforeach
