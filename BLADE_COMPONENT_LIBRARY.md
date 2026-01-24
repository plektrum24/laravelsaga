# Blade Component Library Reference

## Overview
Complete reusable component library for sagatokov3 POS/Inventory system. All components follow Tailwind CSS v4 design system with dark mode support.

## Button Components

### Primary Button
```blade
<x-button.primary>Save Changes</x-button.primary>
<x-button.primary variant="secondary">Cancel</x-button.primary>
<x-button.primary variant="success">Approve</x-button.primary>
<x-button.primary variant="error">Delete</x-button.primary>
<x-button.primary size="lg">Large Button</x-button.primary>
<x-button.primary size="sm">Small Button</x-button.primary>
<x-button.primary :disabled="true">Disabled</x-button.primary>
```

**Props:**
- `variant`: primary, secondary, success, error, warning, info (default: primary)
- `size`: sm, md, lg (default: md)
- `disabled`: Boolean (default: false)
- `icon`: SVG content for icons
- `iconPosition`: left, right (default: left)

### Menu Link
```blade
<x-button.menu-link href="/dashboard" :active="true">Dashboard</x-button.menu-link>
```

**Props:**
- `href`: URL to navigate to
- `active`: Boolean, highlights if current page (default: false)

### Tab Button
```blade
<x-button.tab :active="true">Tab 1</x-button.tab>
<x-button.tab type="secondary">Tab 2</x-button.tab>
```

**Props:**
- `type`: primary, secondary, success, error, warning (default: primary)
- `active`: Boolean (default: false)
- `icon`: SVG content

### Navigation Item
```blade
<x-button.nav-item :active="true" badge="3">
    Dashboard
</x-button.nav-item>
```

**Props:**
- `active`: Boolean (default: false)
- `badge`: Text/number for badge
- `icon`: SVG content

### Dropdown
```blade
<x-button.dropdown>
    <span>Actions</span>
    {{ $slot }}
</x-button.dropdown>
```

**Props:**
- `variant`: primary, secondary (default: primary)
- `isOpen`: Boolean (default: false)

### Dropdown Item
```blade
<x-button.dropdown-item href="/edit">Edit</x-button.dropdown-item>
```

**Props:**
- `href`: URL
- `icon`: SVG content

---

## Form Components

### Input
```blade
<x-form.input 
    name="email" 
    label="Email Address" 
    placeholder="user@example.com"
    type="email"
    required
/>
```

**Props:**
- `name`: Input name/id
- `label`: Label text
- `type`: text, email, password, number, etc. (default: text)
- `placeholder`: Placeholder text
- `value`: Current value
- `error`: Error message (shows in red)
- `required`: Boolean (default: false)
- `disabled`: Boolean (default: false)

### Textarea
```blade
<x-form.textarea 
    name="description" 
    label="Description"
    rows="4"
    placeholder="Enter description"
/>
```

**Props:**
- `name`: Textarea name/id
- `label`: Label text
- `placeholder`: Placeholder text
- `value`: Current value
- `rows`: Number of rows (default: 4)
- `error`: Error message
- `required`: Boolean (default: false)
- `disabled`: Boolean (default: false)

### Select
```blade
<x-form.select 
    name="status" 
    label="Status"
    :options="['active' => 'Active', 'inactive' => 'Inactive']"
    value="active"
/>
```

**Props:**
- `name`: Select name/id
- `label`: Label text
- `options`: Array of value => label pairs
- `value`: Selected value
- `placeholder`: First option text (default: "Select an option")
- `error`: Error message
- `required`: Boolean (default: false)
- `disabled`: Boolean (default: false)

### Checkbox
```blade
<x-form.checkbox 
    name="terms" 
    label="I agree to terms"
    checked
/>
```

**Props:**
- `name`: Checkbox name/id
- `label`: Label text
- `checked`: Boolean (default: false)
- `error`: Error message
- `disabled`: Boolean (default: false)

### Radio
```blade
<x-form.radio 
    name="gender" 
    label="Male"
    value="male"
    checked
/>
```

**Props:**
- `name`: Radio name/id
- `label`: Label text
- `value`: Radio value
- `checked`: Boolean (default: false)
- `error`: Error message
- `disabled`: Boolean (default: false)

---

## Badge Component

### Badge
```blade
<x-badge.badge>Primary</x-badge.badge>
<x-badge.badge variant="success">Active</x-badge.badge>
<x-badge.badge variant="error">Failed</x-badge.badge>
<x-badge.badge variant="warning">Pending</x-badge.badge>
<x-badge.badge variant="info">Info</x-badge.badge>
```

**Props:**
- `variant`: primary, success, error, warning, info (default: primary)

---

## Avatar Component

### Avatar
```blade
<x-avatar.avatar size="md" src="/images/user-01.jpg" alt="John Doe" />
<x-avatar.avatar size="lg" alt="Jane Doe" />
```

**Props:**
- `size`: xs, sm, md, lg, xl, 2xl (default: md)
- `src`: Image URL (optional)
- `alt`: Alt text and fallback initial

---

## Card Component

### Card
```blade
<x-card.card title="Card Title">
    <p>Card content goes here</p>
</x-card.card>

<x-card.card variant="compact">
    No title card
</x-card.card>
```

**Props:**
- `title`: Card title (optional)
- `variant`: default, compact (default: default)

---

## Alert Component

### Alert
```blade
<x-alert.alert type="success" title="Success!">
    Operation completed successfully.
</x-alert.alert>

<x-alert.alert type="error" :dismissible="true">
    An error occurred.
</x-alert.alert>

<x-alert.alert type="warning">
    Please review before continuing.
</x-alert.alert>

<x-alert.alert type="info" :dismissible="false">
    This is informational.
</x-alert.alert>
```

**Props:**
- `type`: success, error, warning, info (default: success)
- `title`: Alert title (optional)
- `dismissible`: Boolean (default: true)

---

## Modal Components

### Modal
```blade
<x-modal.modal title="Create Item" size="md">
    <form>
        <x-form.input name="name" label="Name" />
        <div class="flex gap-2 mt-4">
            <x-button.primary>Save</x-button.primary>
            <x-button.primary variant="secondary">Cancel</x-button.primary>
        </div>
    </form>
</x-modal.modal>
```

**Props:**
- `title`: Modal title
- `size`: sm, md, lg, xl (default: md)

**Usage:** Add `x-data="{ open: false }"` to wrapper and `@click="open = true"` to trigger button.

### Loading Modal
```blade
<x-modal.loading title="Processing..." />
```

**Props:**
- `title`: Loading message (default: "Loading")

### Confirmation Dialog
```blade
<x-modal.confirmation 
    title="Delete Item?"
    message="This action cannot be undone."
    confirmText="Delete"
    cancelText="Cancel"
    variant="error"
    @confirm="deleteItem()"
/>
```

**Props:**
- `title`: Dialog title
- `message`: Confirmation message
- `confirmText`: Confirm button text (default: "Confirm")
- `cancelText`: Cancel button text (default: "Cancel")
- `variant`: warning, error, success, info (default: warning)

---

## Table Components

### Basic Table
```blade
<x-table.table :headers="['Name', 'Email', 'Status']">
    <tr>
        <td class="px-6 py-4">John Doe</td>
        <td class="px-6 py-4">john@example.com</td>
        <td class="px-6 py-4">
            <x-badge.badge variant="success">Active</x-badge.badge>
        </td>
    </tr>
</x-table.table>
```

**Props:**
- `headers`: Array of header names
- `striped`: Boolean (default: true)

### Data Table
```blade
<x-table.data-table 
    :headers="['Name', 'Email', 'Status']"
    :rows="[
        ['John Doe', 'john@example.com', 'Active'],
        ['Jane Smith', 'jane@example.com', 'Inactive'],
    ]"
/>
```

**Props:**
- `headers`: Array of header names
- `rows`: Array of row data (array of arrays)
- `striped`: Boolean (default: true)

---

## Usage Example

```blade
<!-- Full Form Example -->
<x-card.card title="User Form">
    <form action="/users" method="POST" class="space-y-4">
        @csrf
        
        <x-form.input 
            name="name" 
            label="Full Name"
            placeholder="Enter name"
            error="{{ $errors->first('name') }}"
            required
        />
        
        <x-form.input 
            name="email" 
            label="Email"
            type="email"
            error="{{ $errors->first('email') }}"
            required
        />
        
        <x-form.select 
            name="role"
            label="Role"
            :options="['user' => 'User', 'admin' => 'Admin']"
            error="{{ $errors->first('role') }}"
        />
        
        <x-form.checkbox 
            name="active"
            label="Active"
            checked
        />
        
        <div class="flex gap-3 pt-4 border-t">
            <x-button.primary type="submit">Save</x-button.primary>
            <x-button.primary variant="secondary" onclick="history.back()">Cancel</x-button.primary>
        </div>
    </form>
</x-card.card>
```

---

## Component Structure

```
resources/views/components/
├── button/
│   ├── primary.blade.php
│   ├── menu-link.blade.php
│   ├── tab.blade.php
│   ├── nav-item.blade.php
│   ├── dropdown.blade.php
│   └── dropdown-item.blade.php
├── form/
│   ├── input.blade.php
│   ├── textarea.blade.php
│   ├── select.blade.php
│   ├── checkbox.blade.php
│   └── radio.blade.php
├── badge/
│   └── badge.blade.php
├── avatar/
│   └── avatar.blade.php
├── card/
│   └── card.blade.php
├── alert/
│   └── alert.blade.php
├── modal/
│   ├── modal.blade.php
│   ├── loading.blade.php
│   └── confirmation.blade.php
└── table/
    ├── table.blade.php
    └── data-table.blade.php
```

## Dark Mode Support
All components include automatic dark mode support via Tailwind CSS dark mode.
- Light mode classes applied by default
- Dark mode classes prefixed with `dark:`

## Accessibility
Components follow WCAG guidelines:
- Proper semantic HTML (buttons, links, etc.)
- ARIA attributes where needed
- Keyboard navigation support
- Focus states for interactive elements
