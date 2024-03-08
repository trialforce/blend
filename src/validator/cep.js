"use strict"

class ValidatorCep extends Validator.Validator
{
    validate(value)
    {
        let error = super.validate(value);
        this.value = str_replace(['-', '.'], '', this.value);

        if (mb_strlen(this.value) > 0 && !this.validateCep())
        {
            error.push('Cep digitado é inválido');
        }

        return error;
    }

    validateCep()
    {
        if (preg_match(/^[0-9]{8}/, this.value))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    static mask(value)
    {
        let cep = '';
    
        if (value)
        {
            value = Validator.Validator.unmask(value);
            cep = substr(value, 0, 5) + '-' + substr(value, 5, 3);
        }
    
        return cep;
    }

}

Validator.Cep = ValidatorCep;