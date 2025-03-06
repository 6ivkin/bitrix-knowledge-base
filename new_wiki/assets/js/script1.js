document.addEventListener('DOMContentLoaded', function () {
  const isNewsPage = window.location.pathname.includes('/wiki/news')
  const burger = document.querySelector('.burger')
  const placeholder = document.querySelector('.burger-placeholder')
  const sidebar = document.querySelector('.sidebar')

  if (!isNewsPage) {
    // Скрываем бургер и показываем плейсхолдер
    if (burger) burger.style.display = 'none'
    if (placeholder) placeholder.style.display = 'block'
    if (sidebar) sidebar.style.display = 'none'
    return
  }



  // Прячем плейсхолдер для новостной страницы
  if (placeholder) placeholder.style.display = 'none'

  // Остальной код для работы бургера
  const header = document.querySelector('.header')
  const menu = document.querySelector('.header__bottom')
  const newsDetail = document.querySelector('.newsDetail')
  const navigate = document.querySelector('.navigate')
  const top_section = document.querySelector('.top_section')
  const media_size = window.innerWidth;

  if(top_section && media_size < 993) {    
    burger.classList.toggle('burger--open')
      sidebar?.classList.toggle('sidebar--active')
      header?.classList.toggle('header--open')
      menu?.classList.toggle('header__bottom--open')

      const isActive = sidebar?.classList.contains('sidebar--active')
      newsDetail?.classList.toggle('hidden', isActive)
      navigate?.classList.toggle('hidden', isActive)
  }

  if (burger) {
    burger.addEventListener('click', function () {
      this.classList.toggle('burger--open')
      sidebar?.classList.toggle('sidebar--active')
      header?.classList.toggle('header--open')
      menu?.classList.toggle('header__bottom--open')

      const isActive = sidebar?.classList.contains('sidebar--active')
      newsDetail?.classList.toggle('hidden', isActive)
      navigate?.classList.toggle('hidden', isActive)
    })
  }
})

function tabs(dataTab, dataInfo, className) {
  let targetMap1 = document.querySelectorAll(`[${dataTab}]`),
    map1 = document.querySelectorAll(`.${className}`)

  targetMap1.forEach((elem) => {
    elem.addEventListener("click", function () {
      let target = this.getAttribute(dataTab)

      map1.forEach((elem) => {
        elem.classList.remove(`${className}--opacity`, `${className}--display`)
      })

      targetMap1.forEach((elem) => {
        elem.classList.remove("active")
      })

      this.classList.add("active")

      let cat = document.querySelectorAll(`[${dataInfo}="${target}"]`)
      cat.forEach((elem) => {
        elem.classList.add(`${className}--display`)
        setTimeout(() => {
          elem.classList.add(`${className}--opacity`)
        }, 400)
      })
    })
  })
}

if (document.querySelector(".tabInfo")) {
  tabs("data-tabMobileTabs", "data-infoMobileTabs", "tabInfo")
}