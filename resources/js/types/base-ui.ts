import type { Component } from 'vue'

export type FormFieldType = 'text' | 'email' | 'password' | 'textarea' | 'select' | 'multiselect' | 'file' | 'checkbox' | 'radio' | 'toggle' | 'tabs'
export type FormValues = object

export interface FormOption {
    label: string
    value: string
    description?: string
    disabled?: boolean
}

export interface FormFieldSchema<TForm extends FormValues = Record<string, unknown>> {
    name: Extract<keyof TForm, string>
    label: string
    type: FormFieldType
    placeholder?: string
    description?: string
    required?: boolean
    disabled?: boolean
    autocomplete?: string
    readonly?: boolean
    options?: FormOption[]
    multiple?: boolean
    accept?: string
}

export interface FormSectionSchema<TForm extends FormValues = Record<string, unknown>> {
    key: string
    title?: string
    description?: string
    fields: Array<FormFieldSchema<TForm>>
}

export const defineFormFields = <TForm extends FormValues>(fields: Array<FormFieldSchema<TForm>>): Array<FormFieldSchema<TForm>> => fields

export type SortDirection = 'asc' | 'desc'

export interface ServerTableQuery<TSort extends string = string> {
    page: number
    perPage: number
    search?: string
    sortBy?: TSort
    sortDirection?: SortDirection
}

export interface DataTableColumn<TData, TSort extends string = string> {
    key: string
    label: string
    value: (row: TData) => string | number | boolean | null | undefined
    sortable?: boolean
    sortKey?: TSort
    class?: string
    headerClass?: string
}

export interface MobileCardField<TData> {
    key: string
    label: string
    value: (row: TData) => string | number | boolean | null | undefined
    class?: string
}

export interface DataTableRowAction<TData> {
    key: string
    label: string
    icon?: Component
    destructive?: boolean
    disabled?: boolean | ((row: TData) => boolean)
    visible?: boolean | ((row: TData) => boolean)
    onClick: (row: TData) => void
}

export type ToastVariant = 'default' | 'success' | 'error' | 'info' | 'warning'

export interface ToastMessage {
    id: string
    title: string
    description?: string
    variant: ToastVariant
    duration: number
}
