<script setup lang="ts">
import { cn } from '@/lib/utils'
import { Check } from 'lucide-vue-next'
import { CheckboxIndicator, CheckboxRoot } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<{
  checked?: boolean | 'indeterminate' | undefined
  modelValue?: boolean | 'indeterminate' | undefined
  defaultChecked?: boolean
  required?: boolean
  name?: string
  disabled?: boolean
  value?: string
  id?: string
  class?: HTMLAttributes['class']
}>()

const emits = defineEmits<{
  'update:checked': [value: boolean | 'indeterminate']
  'update:modelValue': [value: boolean | 'indeterminate']
}>()

import { getCurrentInstance } from 'vue'

const resolvedValue = computed(() => {
  const instance = getCurrentInstance()
  // Vue 3 type-based props can sometimes coerce missing booleans to false.
  // We check the raw VNode props to see which one was actually provided.
  const rawProps = instance?.vnode.props || {}
  
  if ('modelValue' in rawProps || 'model-value' in rawProps) {
    return props.modelValue
  }
  return props.checked
})
</script>

<template>
  <CheckboxRoot
    data-slot="checkbox"
    :model-value="resolvedValue"
    :default-value="props.defaultChecked"
    :required="props.required"
    :name="props.name"
    :disabled="props.disabled"
    :value="props.value"
    :id="props.id"
    @update:model-value="(val) => {
      emits('update:checked', val as boolean | 'indeterminate')
      emits('update:modelValue', val as boolean | 'indeterminate')
    }"
    :class="
      cn('peer border-input data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground data-[state=checked]:border-primary focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive size-4 shrink-0 rounded-[4px] border shadow-xs transition-shadow outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50',
         props.class)"
  >
    <CheckboxIndicator
      data-slot="checkbox-indicator"
      class="flex items-center justify-center text-current transition-none"
    >
      <slot>
        <Check class="size-3.5" />
      </slot>
    </CheckboxIndicator>
  </CheckboxRoot>
</template>
