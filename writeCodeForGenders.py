gendersString = """Abinary
Agender
Ambigender
Androgyne
Androgynous
Aporagender
Autigender
Bakla
Bigender
Binary
Bissu
Butch
Calabai
Calalai
Cis female
Cis male
Demi-boy
Demiflux
Demigender
Demi-girl
Demi-guy
Demi-man
Demi-woman
Dual gender
Faʻafafine
Female
Female to male
Femme
FTM
Gender bender
Gender diverse
Gender gifted
Genderfae
Genderfluid 
Genderflux
Genderfuck
Genderless
Gender nonconforming
Genderqueer
Gender questioning
Gender variant
Graygender
Hijra
Intergender
Intersex
Kathoey
Māhū
Male
Male to female
Man
Man of trans experience
Maverique
Meta-gender
MTF
Multigender
Muxe
Neither
Neurogender
Neutrois
Non-binary
Non-binary transgender
Omnigender
Other
Pangender
Person of transgendered experience
Polygender
Sekhet
Third gender
Trans female
Trans male
Trans male
Trans person
Trans woman
Transgender female
Transgender male
Transgender man
Transgender person
Transgender woman
Transfeminine
Transmasculine
Transsexual
Transsexual female
Transsexual male
Transsexual man
Transsexual person
Transsexual woman
Travesti
Trigender
Tumtum
Two spirit
Vakasalewalewa
Waria
Winkte
Woman
Woman of trans experience
X-gender
X-jendā
Xenogender"""
def camelCase(string):
    newString = str()
    words = list()

    words = string.split(" ")
    for word in words:
        word[0].upper()
        newString+=word
    return newString
genders = gendersString.split("\n")
for gender in genders:
    print(f'<option value="{gender.lower()}">{camelCase(gender)}</option>')