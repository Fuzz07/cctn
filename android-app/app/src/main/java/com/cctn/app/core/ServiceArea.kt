package com.cctn.app.core

/**
 * The area the network actually covers, mirroring the web registration form.
 *
 * Barangays come from the same list the walk-in form uses
 * (resources/views/admin/walkin/create.blade.php), so a customer registering
 * on the phone picks from exactly the options the office would.
 */
object ServiceArea {

    const val PROVINCE = "Cebu"

    val MUNICIPALITIES = listOf("Bantayan", "Santa Fe", "Madridejos")

    private val BARANGAYS: Map<String, List<String>> = mapOf(
        "Bantayan" to listOf(
            "Atop-atop", "Baigad", "Bantigue", "Baod", "Binaobao", "Botigues", "Doong",
            "Guiwanon", "Hilotongan", "Kabac", "Kabangbang", "Kampingganon", "Kangkaibe",
            "Lipayran", "Luyongbaybay", "Mojon", "Obo-ob", "Patao", "Puting Bato",
            "Sillion", "Suba", "Sulangan", "Sungko", "Ticad",
        ),
        "Santa Fe" to listOf(
            "Balidbid", "Hagdan", "Hilantagaan", "Kinatarkan", "Langub", "Maricaban",
            "Okoy", "Poblacion", "Pooc", "Talisay",
        ),
        "Madridejos" to listOf(
            "Bunakan", "Kangwayan", "Kaongkod", "Kodia", "Maalat", "Malbago", "Mancilang",
            "Pili", "Poblacion", "San Agustin", "Tabagak", "Talangnan", "Tarong", "Tugas",
        ),
    )

    fun barangays(municipality: String): List<String> = BARANGAYS[municipality].orEmpty()

    val GENDERS = listOf("Male", "Female")

    val CIVIL_STATUSES = listOf("Single", "Married", "Widowed", "Legally Separated")
}
