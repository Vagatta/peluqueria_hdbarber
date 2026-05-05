import { nextTick, onBeforeUnmount, onMounted } from 'vue'

export function useReveal(rootSelector = 'body') {
  let io: IntersectionObserver | null = null
  let mo: MutationObserver | null = null

  function observeNode(node: Element) {
    if (!node.classList.contains('reveal')) return
    if (node.classList.contains('is-visible')) return
    io?.observe(node)
  }

  onMounted(() => {
    if (typeof window === 'undefined' || !('IntersectionObserver' in window)) return

    const root = document.querySelector(rootSelector) ?? document.body

    io = new IntersectionObserver((entries) => {
      for (const entry of entries) {
        if (!entry.isIntersecting) continue
        entry.target.classList.add('is-visible')
        io?.unobserve(entry.target)
      }
    }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' })

    // Observe nodes already in DOM (and re-scan after next paint for v-if nodes)
    root.querySelectorAll<HTMLElement>('.reveal').forEach(observeNode)
    nextTick(() => root.querySelectorAll<HTMLElement>('.reveal').forEach(observeNode))

    // Watch for nodes added later (async store loads, v-if renders)
    mo = new MutationObserver((mutations) => {
      for (const mutation of mutations) {
        mutation.addedNodes.forEach((added) => {
          if (!(added instanceof Element)) return
          if (added.classList.contains('reveal')) observeNode(added)
          added.querySelectorAll<HTMLElement>('.reveal').forEach(observeNode)
        })
      }
    })
    mo.observe(root, { childList: true, subtree: true })
  })

  onBeforeUnmount(() => {
    io?.disconnect()
    mo?.disconnect()
    io = null
    mo = null
  })
}
